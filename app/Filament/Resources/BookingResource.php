<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Réservations';
    protected static ?string $navigationLabel = 'Réservations';
    protected static ?string $modelLabel = 'réservation';
    protected static ?string $pluralModelLabel = 'Réservations';
    protected static ?int $navigationSort = 1;

    public const STATUSES = [
        'pending' => 'En attente',
        'expired' => 'Expirée',
        'confirmed' => 'Confirmée',
        'active' => 'En cours',
        'returning' => 'Retour aujourd\'hui',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'dispute' => 'Litige',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Réservation')->columns(2)->schema([
                Forms\Components\TextInput::make('reference')->label('Référence')
                    ->default(fn () => Booking::generateReference())->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')->label('Statut')
                    ->options(self::STATUSES)->default('pending')->required(),
                Forms\Components\Select::make('vehicle_id')->label('Véhicule')
                    ->relationship('vehicle', 'full_name')->searchable()->preload()->required(),
                Forms\Components\Select::make('customer_id')->label('Client')
                    ->relationship('customer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()->preload(),
                Forms\Components\DateTimePicker::make('start_date')->label('Date de début')->required(),
                Forms\Components\DateTimePicker::make('end_date')->label('Date de fin')->required(),
                Forms\Components\TextInput::make('total_days')->label('Nombre de jours')->numeric()->required(),
            ]),
            Forms\Components\Section::make('Montants')->columns(3)->schema([
                Forms\Components\TextInput::make('base_price')->label('Prix de base')->numeric()->required()->suffix('DA'),
                Forms\Components\TextInput::make('options_total')->label('Total options')->numeric()->default(0)->suffix('DA'),
                Forms\Components\TextInput::make('discount_amount')->label('Remise')->numeric()->default(0)->suffix('DA'),
                Forms\Components\TextInput::make('total_price')->label('Total')->numeric()->required()->suffix('DA'),
                Forms\Components\TextInput::make('deposit_amount')->label('Caution')->numeric()->suffix('DA'),
                Forms\Components\TextInput::make('advance_amount')->label('Acompte')->numeric()->suffix('DA'),
            ]),
            Forms\Components\Section::make('Paiement')->columns(3)->schema([
                Forms\Components\Select::make('advance_status')->label('Statut acompte')
                    ->options(['pending' => 'En attente', 'paid' => 'Payé', 'refunded' => 'Remboursé'])->default('pending'),
                Forms\Components\Select::make('payment_status')->label('Statut paiement')
                    ->options(['pending' => 'En attente', 'partial' => 'Partiel', 'paid' => 'Payé', 'refunded' => 'Remboursé'])->default('pending'),
                Forms\Components\Select::make('deposit_status')->label('Statut caution')
                    ->options(['pending' => 'En attente', 'held' => 'Bloquée', 'returned' => 'Restituée', 'partial' => 'Partielle', 'kept' => 'Conservée'])->default('pending'),
            ]),
            Forms\Components\Section::make('Notes')->schema([
                Forms\Components\Textarea::make('internal_notes')->label('Notes internes')->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer.last_name')->label('Client')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name)->searchable(),
                Tables\Columns\TextColumn::make('vehicle.full_name')->label('Véhicule')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Début')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_price')->label('Total')->money('DZD')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'confirmed', 'active' => 'success',
                        'pending', 'returning' => 'warning',
                        'cancelled', 'expired', 'dispute' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options(self::STATUSES),
            ])
            ->actions([
                Tables\Actions\Action::make('contrat')->label('Contrat')->icon('heroicon-m-document-arrow-down')
                    ->color('gray')->openUrlInNewTab()
                    ->url(fn (Booking $record) => route('contract.preview', $record)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
