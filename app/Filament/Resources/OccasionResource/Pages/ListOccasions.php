<?php

namespace App\Filament\Resources\OccasionResource\Pages;

use App\Filament\Resources\OccasionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOccasions extends ListRecords
{
    protected static string $resource = OccasionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->modalWidth('lg')];
    }
}
