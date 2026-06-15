<?php

namespace App\Filament\Platform\Resources\SettingDefinitionResource\Pages;

use App\Filament\Platform\Resources\SettingDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettingDefinition extends EditRecord
{
    protected static string $resource = SettingDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
