<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Parc';
    protected static ?string $navigationLabel = 'Entretien';
    protected static ?string $modelLabel = 'entretien';
    protected static ?string $pluralModelLabel = 'Entretien';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vehicle_id')->label('Véhicule')
                ->relationship('vehicle', 'full_name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('type')->label('Type')->required()
                ->helperText('Ex : Vidange, Pneus, Révision'),
            Forms\Components\TextInput::make('cost')->label('Coût')->numeric()->suffix('DA'),
            Forms\Components\DatePicker::make('date')->label('Date')->default(now())->required(),
            Forms\Components\DatePicker::make('next_due_at')->label('Prochaine échéance'),
            Forms\Components\Textarea::make('notes')->label('Notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.full_name')->label('Véhicule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('cost')->label('Coût')->money('DZD'),
                Tables\Columns\TextColumn::make('date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('next_due_at')->label('Prochaine')->date()->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
