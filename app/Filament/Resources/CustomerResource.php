<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required(),
                Forms\Components\TextInput::make('last_name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('whatsapp'),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\TextInput::make('id_number'),
                Forms\Components\TextInput::make('id_document'),
                Forms\Components\TextInput::make('license_number'),
                Forms\Components\DatePicker::make('license_expiry'),
                Forms\Components\TextInput::make('license_front'),
                Forms\Components\TextInput::make('license_back'),
                Forms\Components\Toggle::make('documents_verified')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_bookings')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_blacklisted')
                    ->required(),
                Forms\Components\Textarea::make('blacklist_reason')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_document')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_expiry')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_front')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_back')
                    ->searchable(),
                Tables\Columns\IconColumn::make('documents_verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bookings')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_blacklisted')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
