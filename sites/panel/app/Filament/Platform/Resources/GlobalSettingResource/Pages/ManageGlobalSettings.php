<?php

namespace App\Filament\Platform\Resources\GlobalSettingResource\Pages;

use App\Filament\Platform\Resources\GlobalSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGlobalSettings extends ManageRecords
{
    protected static string $resource = GlobalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
