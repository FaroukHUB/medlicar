<?php

namespace App\Filament\Resources\ConversationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Discussion';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('body')->label('Message')->required()->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('sender_type')->label('De')->badge()
                    ->formatStateUsing(fn ($state, $record) => $state === 'staff'
                        ? ($record->author?->name ?? 'Agence')
                        : 'Client')
                    ->color(fn ($state) => $state === 'staff' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('body')->label('Message')->wrap(),
                Tables\Columns\TextColumn::make('created_at')->label('Le')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Répondre')->icon('heroicon-m-paper-airplane')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['sender_type'] = 'staff';
                        $data['user_id'] = Auth::id();

                        return $data;
                    })
                    ->after(function () {
                        $this->getOwnerRecord()->update(['last_message_at' => now()]);
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
