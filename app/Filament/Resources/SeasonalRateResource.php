<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonalRateResource\Pages;
use App\Models\SeasonalRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SeasonalRateResource extends Resource
{
    protected static ?string $model = SeasonalRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Tarification';
    protected static ?string $navigationLabel = 'Tarifs saisonniers';
    protected static ?string $modelLabel = 'tarif saisonnier';
    protected static ?string $pluralModelLabel = 'Tarifs saisonniers';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required(),
            Forms\Components\Select::make('vehicle_id')->label('Véhicule')
                ->relationship('vehicle', 'full_name')->searchable()
                ->helperText('Laisser vide pour appliquer à une catégorie'),
            Forms\Components\Select::make('category_id')->label('Catégorie')
                ->relationship('category', 'name')->searchable(),
            Forms\Components\DatePicker::make('start_date')->label('Début')->required(),
            Forms\Components\DatePicker::make('end_date')->label('Fin')->required(),
            Forms\Components\TextInput::make('price_per_day')->label('Prix / jour')->numeric()->required()->suffix('DA'),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label('Début')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label('Fin')->date()->sortable(),
                Tables\Columns\TextColumn::make('price_per_day')->label('Prix / jour')->money('DZD'),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasonalRates::route('/'),
            'create' => Pages\CreateSeasonalRate::route('/create'),
            'edit' => Pages\EditSeasonalRate::route('/{record}/edit'),
        ];
    }
}
