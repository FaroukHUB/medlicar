<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureResource\Pages;
use App\Models\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-thumb-up';
    protected static ?string $navigationGroup = 'Site web';
    protected static ?string $navigationLabel = 'Pourquoi nous choisir';
    protected static ?string $modelLabel = 'atout';
    protected static ?string $pluralModelLabel = 'Pourquoi nous choisir';
    protected static ?int $navigationSort = 3;

    public const ICONS = [
        'heroicon-o-shield-check' => 'Confiance / garantie',
        'heroicon-o-banknotes' => 'Prix',
        'heroicon-o-clock' => '24/7',
        'heroicon-o-truck' => 'Livraison',
        'heroicon-o-sparkles' => 'Qualité',
        'heroicon-o-map-pin' => 'Couverture',
        'heroicon-o-wrench-screwdriver' => 'Entretien',
        'heroicon-o-phone' => 'Support',
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\Select::make('icon')->label('Icône')->options(self::ICONS)
                ->default('heroicon-o-shield-check')->native(false),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
            Forms\Components\TextInput::make('title')->label('Titre')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('description')->label('Description')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                Tables\Columns\IconColumn::make('icon')->label('')->icon(fn ($state) => $state ?: 'heroicon-o-check'),
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(50),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
