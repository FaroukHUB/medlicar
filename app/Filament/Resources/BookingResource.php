<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Agency;
use App\Models\Booking;
use App\Models\Option;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    /** Recalcule jours, prix de base, options, total et acompte. */
    public static function recalculate(Get $get, Set $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        $days = 1;
        if ($start && $end) {
            $days = (int) ceil(Carbon::parse($start)->floatDiffInDays(Carbon::parse($end)));
            $days = max(1, $days);
        }
        $set('total_days', $days);

        $daily = (float) (Vehicle::find($get('vehicle_id'))?->price_per_day ?? 0);
        $base = $days * $daily;
        $set('base_price', $base);

        $optTotal = 0;
        $optIds = $get('selected_options') ?? [];
        if (! empty($optIds)) {
            foreach (Option::whereIn('id', $optIds)->get() as $o) {
                $optTotal += $o->price_type === 'per_day' ? (float) $o->price * $days : (float) $o->price;
            }
        }
        $set('options_total', $optTotal);

        $discount = (float) $get('discount_amount');
        $total = max(0, $base + $optTotal - $discount);
        $set('total_price', $total);

        $pct = (float) (Agency::current()->default_advance_percent ?? 0);
        $set('advance_amount', round($total * $pct / 100));
    }

    public static function form(Form $form): Form
    {
        $recalc = fn (Get $get, Set $set) => self::recalculate($get, $set);

        return $form->schema([
            Forms\Components\Section::make('Réservation')->columns(2)->schema([
                Forms\Components\TextInput::make('reference')->label('Référence')
                    ->default(fn () => Booking::generateReference())->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')->label('Statut')
                    ->options(self::STATUSES)->default('pending')->required(),
                Forms\Components\Select::make('vehicle_id')->label('Véhicule')
                    ->relationship('vehicle', 'full_name')->searchable()->preload()->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $set('deposit_amount', (float) (Vehicle::find($state)?->deposit_amount ?? 0));
                        self::recalculate($get, $set);
                    }),
                Forms\Components\Select::make('customer_id')->label('Client')
                    ->relationship('customer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('first_name')->label('Prénom')->required(),
                        Forms\Components\TextInput::make('last_name')->label('Nom')->required(),
                        Forms\Components\TextInput::make('phone')->label('Téléphone')->required(),
                    ]),
                Forms\Components\DateTimePicker::make('start_date')->label('Date de début')->required()
                    ->live()->afterStateUpdated($recalc),
                Forms\Components\DateTimePicker::make('end_date')->label('Date de fin')->required()
                    ->live()->afterStateUpdated($recalc)
                    ->rule(static function (Get $get, ?Booking $record) {
                        return static function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $vehicleId = $get('vehicle_id');
                            $start = $get('start_date');
                            if (! $vehicleId || ! $start || ! $value) {
                                return;
                            }
                            $overlap = Booking::where('vehicle_id', $vehicleId)
                                ->whereIn('status', ['pending', 'confirmed', 'active', 'returning'])
                                ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                ->where('start_date', '<', $value)
                                ->where('end_date', '>', $start)
                                ->exists();
                            if ($overlap) {
                                $fail('Ce véhicule est déjà réservé sur cette période.');
                            }
                        };
                    }),
                Forms\Components\TextInput::make('total_days')->label('Nombre de jours')->numeric()->readOnly(),
            ]),

            Forms\Components\Section::make('Tarification')->columns(2)->schema([
                Forms\Components\Select::make('selected_options')->label('Options / extras')
                    ->multiple()->options(Option::where('is_active', true)->pluck('name', 'id'))
                    ->live()->afterStateUpdated($recalc),
                Forms\Components\TextInput::make('discount_amount')->label('Remise')->numeric()->default(0)->suffix('DA')
                    ->live(onBlur: true)->afterStateUpdated($recalc),
                Forms\Components\TextInput::make('base_price')->label('Prix de base')->numeric()->readOnly()->suffix('DA'),
                Forms\Components\TextInput::make('options_total')->label('Total options')->numeric()->readOnly()->suffix('DA'),
                Forms\Components\TextInput::make('total_price')->label('Total à payer')->numeric()->required()->readOnly()
                    ->suffix('DA')->extraInputAttributes(['class' => 'font-bold']),
                Forms\Components\TextInput::make('deposit_amount')->label('Caution')->numeric()->suffix('DA'),
            ]),

            Forms\Components\Section::make('Paiement')->columns(3)->schema([
                Forms\Components\TextInput::make('advance_amount')->label('Acompte')->numeric()->suffix('DA')
                    ->helperText('Calculé selon le % défini dans les paramètres'),
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
