<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue — last 12 months';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

        $sums = Payment::query()
            ->where('status', Payment::STATUS_SUCCESSFUL)
            ->where('completed_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', completed_at) as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // MySQL fallback (SQLite uses strftime, MySQL uses DATE_FORMAT). Try both.
        if ($sums->isEmpty()) {
            $sums = Payment::query()
                ->where('status', Payment::STATUS_SUCCESSFUL)
                ->where('completed_at', '>=', now()->subMonths(12)->startOfMonth())
                ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as ym, SUM(amount) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (MK)',
                'data' => $months->map(fn ($m) => (float) ($sums[$m] ?? 0))->all(),
                'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                'borderRadius' => 6,
            ]],
            'labels' => $months->map(fn ($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
