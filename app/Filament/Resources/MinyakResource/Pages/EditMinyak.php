<?php

namespace App\Filament\Resources\MinyakResource\Pages;

use App\Filament\Resources\MinyakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMinyak extends EditRecord
{
    protected static string $resource = MinyakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
