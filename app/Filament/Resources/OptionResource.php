<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OptionResource\Pages;
use App\Models\Option;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationGroup = 'Parc';
    protected static ?string $navigationLabel = 'Options & extras';
    protected static ?string $modelLabel = 'option';
    protected static ?string $pluralModelLabel = 'Options';
    protected static ?int $navigationSort = 5;

    public const PRICE_TYPES = [
        'per_day' => 'Par jour',
        'per_booking' => 'Par réservation',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Identifiant URL')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('price')->label('Prix')->numeric()->default(0)->suffix('DA'),
            Forms\Components\Select::make('price_type')->label('Facturation')->options(self::PRICE_TYPES)->default('per_booking')->required(),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Prix')->money('DZD')->sortable(),
                Tables\Columns\TextColumn::make('price_type')->label('Facturation')->badge()
                    ->formatStateUsing(fn ($state) => self::PRICE_TYPES[$state] ?? $state),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOptions::route('/'),
            'create' => Pages\CreateOption::route('/create'),
            'edit' => Pages\EditOption::route('/{record}/edit'),
        ];
    }
}
