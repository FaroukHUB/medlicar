<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookings extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Réservations récentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(Booking::query()->latest())
            ->paginated([5, 10])
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Réf.')->weight('bold'),
                Tables\Columns\TextColumn::make('customer.last_name')->label('Client')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name ?? '—'),
                Tables\Columns\TextColumn::make('vehicle.full_name')->label('Véhicule'),
                Tables\Columns\TextColumn::make('start_date')->label('Début')->date(),
                Tables\Columns\TextColumn::make('total_price')->label('Total')->money('DZD'),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => BookingResource::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'confirmed', 'active' => 'success',
                        'pending', 'returning' => 'warning',
                        'cancelled', 'expired', 'dispute' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('voir')->label('Voir')->icon('heroicon-m-eye')
                    ->url(fn (Booking $record) => BookingResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
