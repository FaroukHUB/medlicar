<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleTemplateResource\Pages;
use App\Models\VehicleTemplate;
use App\Filament\Resources\VehicleResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleTemplateResource extends Resource
{
    protected static ?string $model = VehicleTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Modèles (specs)';
    protected static ?string $modelLabel = 'modèle';
    protected static ?string $pluralModelLabel = 'Modèles (specs)';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('name')->label('Nom du modèle')->required()->placeholder('Hyundai Tucson')->columnSpanFull(),
            Forms\Components\Select::make('brand_id')->label('Marque')->relationship('brand', 'name')->searchable()->preload(),
            Forms\Components\Select::make('category_id')->label('Catégorie')->relationship('category', 'name')->searchable()->preload(),
            Forms\Components\Select::make('transmission')->label('Boîte')->options(VehicleResource::TRANSMISSIONS),
            Forms\Components\Select::make('fuel_type')->label('Carburant')->options(VehicleResource::FUEL_TYPES),
            Forms\Components\TextInput::make('seats')->label('Places')->numeric()->default(5),
            Forms\Components\TextInput::make('doors')->label('Portes')->numeric()->default(5),
            Forms\Components\TextInput::make('luggage_capacity')->label('Bagages')->numeric(),
            Forms\Components\Toggle::make('has_ac')->label('Climatisation')->default(true),
            Forms\Components\Textarea::make('description')->label('Description')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Modèle')->searchable(),
            Tables\Columns\TextColumn::make('brand.name')->label('Marque'),
            Tables\Columns\TextColumn::make('category.name')->label('Catégorie'),
            Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleTemplates::route('/'),
            'create' => Pages\CreateVehicleTemplate::route('/create'),
            'edit' => Pages\EditVehicleTemplate::route('/{record}/edit'),
        ];
    }
}
