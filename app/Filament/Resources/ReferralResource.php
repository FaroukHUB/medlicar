<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Parrainages';
    protected static ?string $modelLabel = 'parrainage';
    protected static ?string $pluralModelLabel = 'Parrainages';
    protected static ?int $navigationSort = 3;

    public const STATUSES = ['pending' => 'En attente', 'validated' => 'Validé', 'cancelled' => 'Annulé'];

    public static function getNavigationBadge(): ?string
    {
        $c = Referral::where('status', 'pending')->count();

        return $c > 0 ? (string) $c : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')->label('Statut')->options(self::STATUSES)->required(),
            Forms\Components\TextInput::make('reward_amount')->label('Récompense parrain')->numeric()->suffix('DA'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('referrer.last_name')->label('Parrain')
                    ->formatStateUsing(fn ($record) => $record->referrer?->full_name),
                Tables\Columns\TextColumn::make('referred.last_name')->label('Filleul')
                    ->formatStateUsing(fn ($record) => $record->referred?->full_name),
                Tables\Columns\TextColumn::make('booking.reference')->label('Réservation'),
                Tables\Columns\TextColumn::make('reward_amount')->label('Récompense')->money('DZD'),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'validated' => 'success', 'cancelled' => 'danger', default => 'warning' }),
                Tables\Columns\TextColumn::make('created_at')->label('Le')->date()->sortable(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->label('Statut')->options(self::STATUSES)])
            ->actions([
                Tables\Actions\Action::make('valider')->label('Valider')->icon('heroicon-m-check')->color('success')
                    ->visible(fn (Referral $record) => $record->status === 'pending')
                    ->requiresConfirmation()->action(fn (Referral $record) => $record->update(['status' => 'validated'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}
