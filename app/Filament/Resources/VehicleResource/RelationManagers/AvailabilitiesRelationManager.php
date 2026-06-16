<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AvailabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    protected static ?string $title = 'Disponibilités / blocages';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    public const TYPES = [
        'maintenance' => 'Entretien',
        'blocked' => 'Indisponible',
        'reserved' => 'Réservé',
        'other' => 'Autre',
    ];

    public function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\DatePicker::make('start_date')->label('Du')->required(),
            Forms\Components\DatePicker::make('end_date')->label('Au')->required()->after('start_date'),
            Forms\Components\Select::make('type')->label('Motif')->options(self::TYPES)->default('blocked')->required(),
            Forms\Components\TextInput::make('reason')->label('Détail (optionnel)')->placeholder('ex : vidange, prêt, panne…'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('start_date', 'desc')
            // On n'affiche que les blocages manuels ; les périodes réservées viennent des réservations.
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('booking_id'))
            ->columns([
                Tables\Columns\TextColumn::make('start_date')->label('Du')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label('Au')->date()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Motif')->badge()
                    ->formatStateUsing(fn ($state) => self::TYPES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'maintenance' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reason')->label('Détail')->limit(40),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Bloquer une période')->icon('heroicon-m-no-symbol'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun blocage')
            ->emptyStateDescription('Ce véhicule est disponible (hors réservations). Bloquez une période pour un entretien ou une indisponibilité.');
    }
}
