<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('brand_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('model')
                    ->required(),
                Forms\Components\TextInput::make('full_name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('plate'),
                Forms\Components\TextInput::make('vin'),
                Forms\Components\TextInput::make('year'),
                Forms\Components\TextInput::make('price_per_day')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price_per_week')
                    ->numeric(),
                Forms\Components\TextInput::make('price_per_month')
                    ->numeric(),
                Forms\Components\TextInput::make('deposit_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('transmission')
                    ->required(),
                Forms\Components\TextInput::make('fuel_type')
                    ->required(),
                Forms\Components\TextInput::make('seats')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\TextInput::make('doors')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\TextInput::make('luggage_capacity')
                    ->numeric(),
                Forms\Components\Toggle::make('has_ac')
                    ->required(),
                Forms\Components\TextInput::make('mileage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('features')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Textarea::make('gallery')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_week')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_month')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transmission')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fuel_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seats')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doors')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('luggage_capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_ac')
                    ->boolean(),
                Tables\Columns\TextColumn::make('mileage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
