<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingRuleResource\Pages;
use App\Models\PricingRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PricingRuleResource extends Resource
{
    protected static ?string $model = PricingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Tarification';
    protected static ?string $navigationLabel = 'Règles de prix';
    protected static ?string $modelLabel = 'règle de prix';
    protected static ?string $pluralModelLabel = 'Règles de prix';
    protected static ?int $navigationSort = 1;

    public const TYPES = [
        'seasonal' => 'Saisonnière',
        'duration' => 'Selon la durée',
        'early_booking' => 'Réservation anticipée',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Identifiant')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('type')->label('Type')->options(self::TYPES)->required()->live(),
            Forms\Components\DatePicker::make('start_date')->label('Début')
                ->visible(fn (Forms\Get $get) => $get('type') === 'seasonal'),
            Forms\Components\DatePicker::make('end_date')->label('Fin')
                ->visible(fn (Forms\Get $get) => $get('type') === 'seasonal'),
            Forms\Components\TextInput::make('min_days')->label('Jours min.')->numeric()
                ->visible(fn (Forms\Get $get) => $get('type') === 'duration'),
            Forms\Components\TextInput::make('max_days')->label('Jours max.')->numeric()
                ->visible(fn (Forms\Get $get) => $get('type') === 'duration'),
            Forms\Components\Select::make('modifier_type')->label('Type de modification')
                ->options(['percentage' => 'Pourcentage', 'fixed' => 'Montant fixe'])->default('percentage')->required(),
            Forms\Components\TextInput::make('modifier_value')->label('Valeur')->numeric()->required()
                ->helperText('Ex : -10 pour -10%, ou -500 pour -500 DA'),
            Forms\Components\TextInput::make('priority')->label('Priorité')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()
                    ->formatStateUsing(fn ($state) => self::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('modifier_value')->label('Valeur')
                    ->formatStateUsing(fn ($state, $record) => $record->modifier_type === 'percentage' ? "{$state}%" : "{$state} DA"),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPricingRules::route('/'),
            'create' => Pages\CreatePricingRule::route('/create'),
            'edit' => Pages\EditPricingRule::route('/{record}/edit'),
        ];
    }
}
