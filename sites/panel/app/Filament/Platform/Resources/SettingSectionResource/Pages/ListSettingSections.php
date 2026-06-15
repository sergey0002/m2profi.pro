<?php

namespace App\Filament\Platform\Resources\SettingSectionResource\Pages;

use App\Filament\Platform\Resources\SettingSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingSections extends ListRecords
{
    protected static string $resource = SettingSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
