<?php

namespace App\Filament\Resources\AgencyDocResource\Pages;

use App\Filament\Resources\AgencyDocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgencyDoc extends EditRecord
{
    protected static string $resource = AgencyDocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
