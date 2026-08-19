<?php

namespace App\Filament\Pages;

use App\Services\Storage\StorageStatsService;
use Filament\Actions\Action;
use Filament\Pages\Page;

class Storage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Storage';
    protected static ?int $navigationSort = 20;
    protected static string $view = 'filament.pages.storage';

    public array $stats = [];

    public function mount(): void
    {
        $this->stats = app(StorageStatsService::class)->stats();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    app(StorageStatsService::class)->forget();
                    $this->stats = app(StorageStatsService::class)->stats();
                }),
        ];
    }
}
