<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource\RelationManagers\MessagesRelationManager;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Clients';
    protected static ?string $navigationLabel = 'Messages clients';
    protected static ?string $modelLabel = 'conversation';
    protected static ?string $pluralModelLabel = 'Conversations';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Conversation::where('status', 'open')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')->label('Client')
                ->relationship('customer', 'last_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload(),
            Forms\Components\Select::make('booking_id')->label('Réservation liée')
                ->relationship('booking', 'reference')->searchable(),
            Forms\Components\TextInput::make('subject')->label('Sujet'),
            Forms\Components\Select::make('status')->label('Statut')
                ->options(['open' => 'Ouverte', 'closed' => 'Fermée'])->default('open')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_message_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.last_name')->label('Client')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name ?? '—')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Sujet')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('messages_count')->label('Messages')->counts('messages'),
                Tables\Columns\TextColumn::make('last_message_at')->label('Dernier message')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'open' ? 'Ouverte' : 'Fermée')
                    ->color(fn ($state) => $state === 'open' ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Statut')
                    ->options(['open' => 'Ouverte', 'closed' => 'Fermée']),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('Ouvrir')])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [MessagesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
