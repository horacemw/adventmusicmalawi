<?php

namespace App\Filament\Resources\PoemResource\Pages;

use App\Filament\Resources\PoemResource;
use App\Models\Poem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoem extends EditRecord
{
    protected static string $resource = PoemResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === Poem::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
