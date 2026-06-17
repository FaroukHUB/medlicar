<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvantageResource\Pages;
use App\Models\Advantage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdvantageResource extends Resource
{
    protected static ?string $model = Advantage::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Site web';
    protected static ?string $navigationLabel = 'Avantages';
    protected static ?string $modelLabel = 'avantage';
    protected static ?string $pluralModelLabel = 'Avantages';
    protected static ?int $navigationSort = 2;

    /** Icônes proposées (heroicons) pour les avantages. */
    public const ICONS = [
        'heroicon-o-check-circle' => 'Coche',
        'heroicon-o-truck' => 'Livraison',
        'heroicon-o-shield-check' => 'Assurance / garantie',
        'heroicon-o-clock' => 'Disponibilité 24/7',
        'heroicon-o-wrench-screwdriver' => 'Entretien',
        'heroicon-o-key' => 'Sans caution / clés',
        'heroicon-o-map-pin' => 'Localisation',
        'heroicon-o-document-text' => 'Documents',
        'heroicon-o-banknotes' => 'Prix / paiement',
        'heroicon-o-sparkles' => 'Premium',
    ];

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('name')->label('Avantage')->required()
                ->placeholder('Ex : Livraison gratuite dans la wilaya')->columnSpanFull(),
            Forms\Components\Select::make('icon')->label('Icône')->options(self::ICONS)
                ->default('heroicon-o-check-circle')->native(false),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\IconColumn::make('icon')->label('')->icon(fn ($state) => $state ?: 'heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('name')->label('Avantage')->searchable(),
                Tables\Columns\TextColumn::make('vehicles_count')->counts('vehicles')->label('Véhicules')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvantages::route('/'),
            'create' => Pages\CreateAdvantage::route('/create'),
            'edit' => Pages\EditAdvantage::route('/{record}/edit'),
        ];
    }
}
