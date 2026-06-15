<?php

namespace App\Filament\Platform\Resources\SettingDefinitionResource\Pages;

use App\Filament\Platform\Resources\SettingDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingDefinitions extends ListRecords
{
    protected static string $resource = SettingDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
