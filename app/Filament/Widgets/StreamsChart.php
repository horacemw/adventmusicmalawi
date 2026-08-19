<?php

namespace App\Filament\Widgets;

use App\Models\Stream;
use Filament\Widgets\ChartWidget;

class StreamsChart extends ChartWidget
{
    protected static ?string $heading = 'Streams — last 30 days';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $counts = Stream::query()
            ->where('counted', true)
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->groupBy('day')
            ->pluck('c', 'day');

        return [
            'datasets' => [[
                'label' => 'Counted streams',
                'data' => $days->map(fn ($d) => (int) ($counts[$d] ?? 0))->all(),
                'borderColor' => '#16a34a',
                'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
