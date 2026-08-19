<?php

namespace App\Filament\Resources\PoemResource\Pages;

use App\Filament\Resources\PoemResource;
use App\Models\Poem;
use Filament\Resources\Pages\CreateRecord;

class CreatePoem extends CreateRecord
{
    protected static string $resource = PoemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploader_id'] = auth()->id();
        if (($data['status'] ?? null) === Poem::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        return $data;
    }
}
