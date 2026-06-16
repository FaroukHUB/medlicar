<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?string $navigationLabel = 'Factures';
    protected static ?string $modelLabel = 'facture';
    protected static ?string $pluralModelLabel = 'Factures';
    protected static ?int $navigationSort = 3;

    public const STATUSES = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyée',
        'paid' => 'Payée',
        'cancelled' => 'Annulée',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('number')->label('Numéro')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')->label('Statut')->options(self::STATUSES)->default('draft')->required(),
                Forms\Components\Select::make('customer_id')->label('Client')
                    ->relationship('customer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)->searchable(),
                Forms\Components\Select::make('booking_id')->label('Réservation')
                    ->relationship('booking', 'reference')->searchable(),
                Forms\Components\DatePicker::make('issued_at')->label('Date d\'émission')->default(now())->required(),
                Forms\Components\DatePicker::make('due_at')->label('Échéance'),
                Forms\Components\TextInput::make('subtotal')->label('Sous-total')->numeric()->default(0)->suffix('DA'),
                Forms\Components\TextInput::make('vat_amount')->label('TVA')->numeric()->default(0)->suffix('DA'),
                Forms\Components\TextInput::make('total')->label('Total')->numeric()->default(0)->suffix('DA'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('N°')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer.last_name')->label('Client')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name),
                Tables\Columns\TextColumn::make('issued_at')->label('Émise le')->date()->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('DZD')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success', 'sent' => 'warning', 'cancelled' => 'danger', default => 'gray',
                    }),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->label('Statut')->options(self::STATUSES)])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
