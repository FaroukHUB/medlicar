<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('contrat')->label('Télécharger le contrat')
                ->icon('heroicon-m-document-arrow-down')->color('success')->openUrlInNewTab()
                ->url(fn () => route('contract.download', $this->record)),
            Actions\DeleteAction::make(),
        ];
    }
}
