<?php

namespace App\Filament\Resources\MusicGroupResource\Pages;

use App\Filament\Resources\MusicGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMusicGroup extends EditRecord
{
    protected static string $resource = MusicGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
