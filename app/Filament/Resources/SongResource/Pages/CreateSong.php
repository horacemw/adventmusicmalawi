<?php

namespace App\Filament\Resources\SongResource\Pages;

use App\Filament\Resources\SongResource;
use App\Services\Songs\AdminSongUploaderService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSong extends CreateRecord
{
    protected static string $resource = SongResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AdminSongUploaderService::class)->create($data, auth()->id());
    }
}
