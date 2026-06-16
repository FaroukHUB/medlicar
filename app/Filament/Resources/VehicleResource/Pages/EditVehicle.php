<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\VehicleResource\Widgets\VehicleCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            VehicleCalendarWidget::class,
        ];
    }
}
