<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatResource\Pages;
use App\Models\Stat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatResource extends Resource
{
    protected static ?string $model = Stat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Site web';
    protected static ?string $navigationLabel = 'Statistiques';
    protected static ?string $modelLabel = 'statistique';
    protected static ?string $pluralModelLabel = 'Statistiques';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->columns(2)->schema([
            Forms\Components\TextInput::make('value')->label('Chiffre')->required()->placeholder('500+'),
            Forms\Components\TextInput::make('label')->label('Libellé')->required()->placeholder('Clients satisfaits'),
            Forms\Components\TextInput::make('icon')->label('Emoji / icône')->placeholder('😀')->maxLength(10),
            Forms\Components\TextInput::make('sort_order')->label('Ordre')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('sort_order')->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('value')->label('Chiffre')->weight('bold'),
                Tables\Columns\TextColumn::make('label')->label('Libellé')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStats::route('/'),
            'create' => Pages\CreateStat::route('/create'),
            'edit' => Pages\EditStat::route('/{record}/edit'),
        ];
    }
}
