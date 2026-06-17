<?php

namespace App\Filament\Resources\VehicleTemplateResource\Pages;

use App\Filament\Resources\VehicleTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleTemplates extends ListRecords
{
    protected static string $resource = VehicleTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
