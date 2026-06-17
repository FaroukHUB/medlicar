<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryLocationResource\Pages;
use App\Models\DeliveryLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryLocationResource extends Resource
{
    protected static ?string $model = DeliveryLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Lieux de livraison';
    protected static ?string $modelLabel = 'lieu de livraison';
    protected static ?string $pluralModelLabel = 'Lieux de livraison';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('name')->label('Nom du lieu')->required()
                ->placeholder('Aéroport d\'Alger')->columnSpanFull(),
            Forms\Components\TextInput::make('description')->label('Adresse / précision')->columnSpanFull(),
            Forms\Components\Toggle::make('is_free')->label('Livraison gratuite')->live()->default(false),
            Forms\Components\TextInput::make('price')->label('Frais de livraison')->numeric()->default(0)->suffix('DA')
                ->visible(fn (Forms\Get $get) => ! $get('is_free')),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true)
                ->helperText('Seuls les lieux actifs apparaissent côté client.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Lieu')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Adresse')->limit(40),
                Tables\Columns\TextColumn::make('price')->label('Frais')
                    ->formatStateUsing(fn ($state, $record) => $record->is_free ? 'Gratuit' : number_format($state, 0, ',', ' ') . ' DA')
                    ->badge()->color(fn ($record) => $record->is_free ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryLocations::route('/'),
            'create' => Pages\CreateDeliveryLocation::route('/create'),
            'edit' => Pages\EditDeliveryLocation::route('/{record}/edit'),
        ];
    }
}
