<?php

namespace App\Filament\Pages;

use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Parametres extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?string $title = 'Paramètres de l\'agence';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.parametres';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Agency::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')->columns(2)->schema([
                    Forms\Components\TextInput::make('name')->label('Nom de l\'agence')->required(),
                    Forms\Components\TextInput::make('legal_name')->label('Raison sociale'),
                    Forms\Components\FileUpload::make('logo')->label('Logo')->image()->directory('agency'),
                    Forms\Components\TextInput::make('phone')->label('Téléphone')->tel(),
                    Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->tel(),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('address')->label('Adresse'),
                    Forms\Components\TextInput::make('city')->label('Ville'),
                    Forms\Components\TextInput::make('wilaya')->label('Wilaya'),
                ]),
                Forms\Components\Section::make('Paramètres métier')->columns(2)->schema([
                    Forms\Components\TextInput::make('currency')->label('Devise')->default('DZD')->maxLength(3),
                    Forms\Components\TextInput::make('vat_rate')->label('Taux de TVA (%)')->numeric(),
                    Forms\Components\TextInput::make('default_advance_percent')->label('Acompte par défaut (%)')->numeric(),
                    Forms\Components\TextInput::make('advance_expiry_hours')->label('Expiration acompte (heures)')->numeric(),
                    Forms\Components\Toggle::make('show_eur')->label('Afficher aussi les prix en euros (€)')
                        ->helperText('Pratique pour la diaspora. Ex : 9 000 DA / 50 €.')->live(),
                    Forms\Components\TextInput::make('eur_rate')->label('Taux : 1 € = ? DA')
                        ->numeric()->placeholder('180')
                        ->helperText('Ex : 180 si 1 € = 180 DA.')
                        ->visible(fn (Forms\Get $get) => $get('show_eur')),
                    Forms\Components\Textarea::make('contract_terms')->label('Conditions du contrat (CGV)')->columnSpanFull()->rows(5),
                ]),
                Forms\Components\Section::make('Paiement en ligne (PayPal)')
                    ->description('Permet au client de régler son acompte en ligne. PayPal ne gère pas le dinar : indiquez la devise et le taux de conversion.')
                    ->columns(2)->collapsed()->schema([
                        Forms\Components\Toggle::make('paypal_enabled')->label('Activer le paiement PayPal')->columnSpanFull(),
                        Forms\Components\Select::make('paypal_mode')->label('Mode')
                            ->options(['sandbox' => 'Test (sandbox)', 'live' => 'Production (réel)'])->default('sandbox'),
                        Forms\Components\Select::make('paypal_currency')->label('Devise PayPal')
                            ->options(['EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP'])->default('EUR'),
                        Forms\Components\TextInput::make('paypal_rate')->label('Taux : 1 unité de devise = ? DA')
                            ->numeric()->helperText('Ex : 150 si 1 EUR = 150 DA. L\'acompte en DA sera converti automatiquement.'),
                        Forms\Components\TextInput::make('paypal_client_id')->label('Client ID')->password()->revealable(),
                        Forms\Components\TextInput::make('paypal_secret')->label('Secret')->password()->revealable()->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Modules')
                    ->description('Active les services proposés par l\'agence. Désactivés = location classique uniquement.')
                    ->columns(2)->schema([
                        Forms\Components\Toggle::make('delivery_enabled')->label('Livraison de véhicule'),
                        Forms\Components\Toggle::make('transfer_enabled')->label('Transfert / VTC'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Agency::current()->update($this->form->getState());

        Notification::make()->title('Paramètres enregistrés')->success()->send();
    }
}
