<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Clients';
    protected static ?string $navigationLabel = 'Avis';
    protected static ?string $modelLabel = 'avis';
    protected static ?string $pluralModelLabel = 'Avis';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')->label('Client')
                ->relationship('customer', 'last_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)->searchable(),
            Forms\Components\Select::make('vehicle_id')->label('Véhicule')
                ->relationship('vehicle', 'full_name')->searchable(),
            Forms\Components\Select::make('rating')->label('Note')
                ->options([1 => '1 ★', 2 => '2 ★', 3 => '3 ★', 4 => '4 ★', 5 => '5 ★'])->default(5)->required(),
            Forms\Components\Textarea::make('comment')->label('Commentaire')->columnSpanFull(),
            Forms\Components\Toggle::make('is_published')->label('Publié'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.last_name')->label('Client')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name),
                Tables\Columns\TextColumn::make('vehicle.full_name')->label('Véhicule'),
                Tables\Columns\TextColumn::make('rating')->label('Note')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state)),
                Tables\Columns\IconColumn::make('is_published')->label('Publié')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date()->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_published')->label('Publié')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
