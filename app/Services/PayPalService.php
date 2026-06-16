<?php

namespace App\Services;

use App\Models\Agency;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    public function __construct(private Agency $agency) {}

    public function isConfigured(): bool
    {
        return $this->agency->paypal_enabled
            && ! empty($this->agency->paypal_client_id)
            && ! empty($this->agency->paypal_secret);
    }

    private function baseUrl(): string
    {
        return $this->agency->paypal_mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /** Devise PayPal (EUR/USD…) — DZD non supporté. */
    public function currency(): string
    {
        return $this->agency->paypal_currency ?: 'EUR';
    }

    /** Convertit un montant en DA vers la devise PayPal selon le taux configuré. */
    public function convertFromDa(float $amountDa): float
    {
        $rate = (float) ($this->agency->paypal_rate ?: 0);

        return $rate > 0 ? round($amountDa / $rate, 2) : round($amountDa, 2);
    }

    private function accessToken(): ?string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->agency->paypal_client_id, $this->agency->paypal_secret)
            ->post($this->baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        return $response->successful() ? $response->json('access_token') : null;
    }

    /**
     * Crée une commande PayPal. Renvoie ['id' => ..., 'approve_url' => ...] ou null.
     */
    public function createOrder(float $amount, string $reference, string $returnUrl, string $cancelUrl): ?array
    {
        $token = $this->accessToken();
        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)->post($this->baseUrl() . '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $reference,
                'description' => 'Acompte réservation ' . $reference,
                'amount' => [
                    'currency_code' => $this->currency(),
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'brand_name' => $this->agency->name,
                'user_action' => 'PAY_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        $approveUrl = collect($response->json('links', []))
            ->firstWhere('rel', 'approve')['href'] ?? null;

        return ['id' => $response->json('id'), 'approve_url' => $approveUrl];
    }

    /** Capture (encaisse) une commande approuvée. Vrai si paiement complété. */
    public function captureOrder(string $orderId): bool
    {
        $token = $this->accessToken();
        if (! $token) {
            return false;
        }

        $response = Http::withToken($token)
            ->post($this->baseUrl() . "/v2/checkout/orders/{$orderId}/capture");

        return $response->successful() && $response->json('status') === 'COMPLETED';
    }
}
