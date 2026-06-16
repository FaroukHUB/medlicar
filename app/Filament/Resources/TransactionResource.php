<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?string $navigationLabel = 'Recettes & dépenses';
    protected static ?string $modelLabel = 'transaction';
    protected static ?string $pluralModelLabel = 'Recettes & dépenses';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->label('Type')
                ->options(['income' => 'Recette', 'expense' => 'Dépense'])->default('expense')->required()->live(),
            Forms\Components\TextInput::make('description')->label('Description')->required(),
            Forms\Components\TextInput::make('amount')->label('Montant')->numeric()->required()->suffix('DA'),
            Forms\Components\DatePicker::make('transaction_date')->label('Date')->default(now())->required(),
            Forms\Components\Select::make('expense_category_id')->label('Catégorie de dépense')
                ->relationship('expenseCategory', 'name')->searchable()
                ->visible(fn (Forms\Get $get) => $get('type') === 'expense'),
            Forms\Components\Select::make('vehicle_id')->label('Véhicule concerné')
                ->relationship('vehicle', 'full_name')->searchable(),
            Forms\Components\TextInput::make('payment_method')->label('Moyen de paiement'),
            Forms\Components\Textarea::make('notes')->label('Notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'income' ? 'Recette' : 'Dépense')
                    ->color(fn ($state) => $state === 'income' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('description')->label('Description')->searchable(),
                Tables\Columns\TextColumn::make('expenseCategory.name')->label('Catégorie')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('DZD')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Type')
                    ->options(['income' => 'Recette', 'expense' => 'Dépense']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
