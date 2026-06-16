<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->required(),
                Forms\Components\TextInput::make('vehicle_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('customer_id')
                    ->numeric(),
                Forms\Components\TextInput::make('created_by')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('start_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->required(),
                Forms\Components\TextInput::make('total_days')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('pickup_location'),
                Forms\Components\TextInput::make('return_location'),
                Forms\Components\TextInput::make('flight_number'),
                Forms\Components\Textarea::make('pickup_notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('return_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('base_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('duration_discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('season_surcharge')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('options_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('extra_fees')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required(),
                Forms\Components\Textarea::make('selected_options')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('deposit_amount')
                    ->numeric(),
                Forms\Components\TextInput::make('deposit_status')
                    ->required(),
                Forms\Components\Textarea::make('deposit_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('advance_amount')
                    ->numeric(),
                Forms\Components\TextInput::make('advance_status')
                    ->required(),
                Forms\Components\TextInput::make('advance_payment_method'),
                Forms\Components\DateTimePicker::make('advance_paid_at'),
                Forms\Components\DateTimePicker::make('advance_expires_at'),
                Forms\Components\TextInput::make('amount_paid')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('amount_remaining')
                    ->numeric(),
                Forms\Components\TextInput::make('payment_method'),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\Textarea::make('photos_before')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('photos_after')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('condition_notes_before')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('condition_notes_after')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('mileage_start')
                    ->numeric(),
                Forms\Components\TextInput::make('mileage_end')
                    ->numeric(),
                Forms\Components\TextInput::make('fuel_level_start'),
                Forms\Components\TextInput::make('fuel_level_end'),
                Forms\Components\DateTimePicker::make('contract_signed_at'),
                Forms\Components\TextInput::make('contract_signature'),
                Forms\Components\TextInput::make('contract_pdf'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('cancelled_at'),
                Forms\Components\TextInput::make('cancelled_by'),
                Forms\Components\TextInput::make('source')
                    ->required(),
                Forms\Components\Textarea::make('internal_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('return_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('season_surcharge')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('options_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_fee')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_fees')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deposit_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('advance_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('advance_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('advance_payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('advance_paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('advance_expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_remaining')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mileage_start')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mileage_end')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fuel_level_start')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fuel_level_end')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract_signed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_signature')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract_pdf')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cancelled_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
