<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Agency;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['amount_remaining'] = max(0, (float) ($data['total_price'] ?? 0) - (float) ($data['advance_amount'] ?? 0));

        // Délai d'expiration de l'acompte (timer configurable)
        if (! empty($data['advance_amount']) && empty($data['advance_paid_at'])) {
            $hours = (int) (Agency::current()->advance_expiry_hours ?? 48);
            $data['advance_expires_at'] = now()->addHours($hours);
        }

        return $data;
    }
}
