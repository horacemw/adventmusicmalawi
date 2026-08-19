<?php

namespace App\Filament\Resources\SongResource\Pages;

use App\Filament\Resources\SongResource;
use App\Services\Songs\AdminSongUploaderService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSong extends EditRecord
{
    protected static string $resource = SongResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $data['category_ids'] = $record->categories->pluck('id')->all();
        $data['occasion_ids'] = $record->occasions->pluck('id')->all();
        $data['mood_ids'] = $record->moods->pluck('id')->all();
        $data['featured_artist_ids'] = $record->featuredArtists->pluck('id')->all();
        if ($record->copyright) {
            $data['copyright_owner'] = $record->copyright->copyright_owner;
            $data['rights_holder'] = $record->copyright->rights_holder;
            $data['permission_status'] = $record->copyright->permission_status;
            $data['license_type'] = $record->copyright->license_type;
            $data['distribution_allowed'] = $record->copyright->distribution_allowed;
            $data['monetization_allowed'] = $record->copyright->monetization_allowed;
            $data['copyright_notes'] = $record->copyright->notes;
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(AdminSongUploaderService::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
