<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Site web';
    protected static ?string $navigationLabel = 'Bannières';
    protected static ?string $modelLabel = 'bannière';
    protected static ?string $pluralModelLabel = 'Bannières';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Visuel')->schema([
                Forms\Components\FileUpload::make('image')->label('Image de fond')
                    ->image()->directory('hero')->imageEditor()
                    ->helperText('Format paysage recommandé (ex : 1920 × 800 px).')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Contenu')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->label('Titre')
                    ->placeholder('Louez votre voiture en quelques clics')->columnSpanFull(),
                Forms\Components\TextInput::make('subtitle')->label('Sous-titre')
                    ->placeholder('Choisissez, réservez, roulez.')->columnSpanFull(),
                Forms\Components\TextInput::make('button_text')->label('Texte du bouton')->placeholder('Voir nos véhicules'),
                Forms\Components\TextInput::make('button_url')->label('Lien du bouton')
                    ->placeholder('#vehicules ou https://…')->helperText('Laisse vide pour faire défiler vers les véhicules.'),
            ]),
            Forms\Components\Section::make('Affichage')->columns(2)->schema([
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Image'),
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('button_text')->label('Bouton'),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
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
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
