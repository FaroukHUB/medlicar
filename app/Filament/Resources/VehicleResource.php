<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Mes Véhicules';
    protected static ?string $modelLabel = 'véhicule';
    protected static ?string $pluralModelLabel = 'Véhicules';
    protected static ?int $navigationSort = 1;

    public const FUEL_TYPES = [
        'diesel' => 'Diesel',
        'essence' => 'Essence',
        'hybrid' => 'Hybride',
        'electric' => 'Électrique',
        'gpl' => 'GPL',
    ];

    public const TRANSMISSIONS = [
        'automatic' => 'Automatique',
        'manual' => 'Manuelle',
    ];

    public const STATUSES = [
        'available' => 'Disponible',
        'rented' => 'Loué',
        'maintenance' => 'En maintenance',
        'unavailable' => 'Indisponible',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('brand_id')
                            ->label('Marque')
                            ->relationship('brand', 'name')
                            ->searchable()->preload()->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()->preload()->required(),
                        Forms\Components\TextInput::make('model')
                            ->label('Modèle')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nom complet')
                            ->helperText('Ex : VW Tiguan 2025')
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->label('Identifiant URL')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('year')->label('Année'),
                        Forms\Components\TextInput::make('plate')->label('Immatriculation'),
                        Forms\Components\TextInput::make('vin')->label('N° de série (VIN)'),
                    ]),

                Forms\Components\Section::make('Tarification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('price_per_day')
                            ->label('Prix / jour')->numeric()->required()->suffix('DA'),
                        Forms\Components\TextInput::make('price_per_week')
                            ->label('Prix / semaine')->numeric()->suffix('DA'),
                        Forms\Components\TextInput::make('price_per_month')
                            ->label('Prix / mois')->numeric()->suffix('DA'),
                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Caution')->numeric()->default(0)->suffix('DA'),
                    ]),

                Forms\Components\Section::make('Caractéristiques')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('transmission')
                            ->label('Boîte')->options(self::TRANSMISSIONS)->default('automatic')->required(),
                        Forms\Components\Select::make('fuel_type')
                            ->label('Carburant')->options(self::FUEL_TYPES)->default('diesel')->required(),
                        Forms\Components\TextInput::make('seats')
                            ->label('Places')->numeric()->default(5)->required(),
                        Forms\Components\TextInput::make('doors')
                            ->label('Portes')->numeric()->default(5)->required(),
                        Forms\Components\TextInput::make('luggage_capacity')
                            ->label('Bagages')->numeric(),
                        Forms\Components\TextInput::make('mileage')
                            ->label('Kilométrage')->numeric()->default(0)->suffix('km'),
                        Forms\Components\Toggle::make('has_ac')->label('Climatisation')->default(true),
                    ]),

                Forms\Components\Section::make('Photos & statut')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Photo principale')->image()->directory('vehicles'),
                        Forms\Components\FileUpload::make('gallery')
                            ->label('Galerie')->image()->multiple()->directory('vehicles')->reorderable(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')->options(self::STATUSES)->default('available')->required(),
                        Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Mise en avant & avantages')
                    ->description('Pilote l\'affichage de ce véhicule sur le site public.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Coup de cœur')
                            ->helperText('Apparaît dans la section « Notre sélection ».'),
                        Forms\Components\Toggle::make('is_on_promo')
                            ->label('En promo')->live(),
                        Forms\Components\TextInput::make('promo_label')
                            ->label('Texte du badge promo')->placeholder('PROMO')
                            ->visible(fn (Forms\Get $get) => $get('is_on_promo'))
                            ->maxLength(20),
                        Forms\Components\Select::make('advantages')
                            ->label('Avantages affichés')
                            ->relationship('advantages', 'name')
                            ->multiple()->preload()
                            ->helperText('Choisis dans ton catalogue d\'avantages (Site web → Avantages).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Photo'),
                Tables\Columns\TextColumn::make('full_name')->label('Véhicule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->label('Marque')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Catégorie')->sortable(),
                Tables\Columns\TextColumn::make('plate')->label('Immat.')->searchable(),
                Tables\Columns\TextColumn::make('price_per_day')->label('Prix / jour')
                    ->money('DZD')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'available' => 'success',
                        'rented' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')->options(self::STATUSES),
                Tables\Filters\SelectFilter::make('category_id')->label('Catégorie')
                    ->relationship('category', 'name'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
