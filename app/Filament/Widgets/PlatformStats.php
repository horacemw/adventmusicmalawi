<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicGroup;
use App\Models\Payment;
use App\Models\Poem;
use App\Models\Song;
use App\Models\Stream;
use App\Models\Submission;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $revenueMtd = Payment::query()
            ->where('status', Payment::STATUS_SUCCESSFUL)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('amount');

        $streamsMonth = Stream::query()
            ->where('counted', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $storageMb = $this->storageUsedMb();

        return [
            Stat::make('Songs published', Song::published()->count())
                ->description('Live on the site')
                ->descriptionIcon('heroicon-m-musical-note')
                ->color('success'),

            Stat::make('Artists & choirs', Artist::count() + MusicGroup::count())
                ->description(MusicGroup::count().' groups · '.Artist::count().' artists')
                ->descriptionIcon('heroicon-m-microphone')
                ->color('primary'),

            Stat::make('Registered users', User::count())
                ->description(User::whereNotNull('email_verified_at')->count().' verified')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Streams this month', number_format($streamsMonth))
                ->description(number_format(Stream::where('counted', true)->count()).' all-time')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),

            Stat::make('Pending submissions', Submission::where('status', Submission::STATUS_UNDER_REVIEW)->count())
                ->description(Submission::where('status', Submission::STATUS_APPROVED)->count().' approved · '.Submission::where('status', Submission::STATUS_REJECTED)->count().' rejected')
                ->descriptionIcon('heroicon-m-inbox')
                ->color(Submission::where('status', Submission::STATUS_UNDER_REVIEW)->count() > 0 ? 'warning' : 'gray'),

            Stat::make('Revenue this month', 'MK '.number_format($revenueMtd))
                ->description('MK '.number_format(Payment::where('status', Payment::STATUS_SUCCESSFUL)->sum('amount')).' all-time')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Albums', Album::count())
                ->description(Album::where('is_published', true)->count().' published')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Poems', Poem::count())
                ->description(Poem::published()->count().' published')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Storage used', $storageMb < 1024 ? number_format($storageMb, 1).' MB' : number_format($storageMb / 1024, 2).' GB')
                ->description('Audio + artwork on disk')
                ->descriptionIcon('heroicon-m-server-stack')
                ->color('gray'),

            Stat::make('Successful payments', Payment::where('status', Payment::STATUS_SUCCESSFUL)->count())
                ->description(Payment::where('status', Payment::STATUS_FAILED)->count().' failed · '.Payment::where('status', Payment::STATUS_PENDING)->count().' pending')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Content growth (7d)', '+'.Song::where('created_at', '>=', now()->subDays(7))->count().' songs')
                ->description('+'.User::where('created_at', '>=', now()->subDays(7))->count().' users · +'.MusicGroup::where('created_at', '>=', now()->subDays(7))->count().' groups')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }

    private function storageUsedMb(): float
    {
        // Cheap approximation from DB columns to avoid scanning disk on every dashboard hit
        $audio = Song::sum('audio_size_bytes') ?? 0;
        $submissions = \App\Models\SubmissionFile::sum('size_bytes') ?? 0;
        return round(($audio + $submissions) / 1024 / 1024, 2);
    }
}
