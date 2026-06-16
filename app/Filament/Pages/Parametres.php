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
                    Forms\Components\Textarea::make('contract_terms')->label('Conditions du contrat (CGV)')->columnSpanFull()->rows(5),
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
