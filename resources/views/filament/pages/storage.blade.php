@php
    $format = function (int $bytes) {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min(floor(log($bytes, 1024)), count($units) - 1);
        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    };
    $usedPct = $stats['used_percentage'] ?? 0;
    $totalBytes = $stats['total_bytes'] ?? 0;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <x-filament::section>
                <p class="text-xs uppercase tracking-wider text-gray-500">Audio</p>
                <p class="text-2xl font-bold mt-1">{{ $format($stats['audio_bytes']) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['song_count'] }} songs</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-xs uppercase tracking-wider text-gray-500">Artwork</p>
                <p class="text-2xl font-bold mt-1">{{ $format($stats['artwork_bytes']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Covers & profile images</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-xs uppercase tracking-wider text-gray-500">Submission files</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['submission_file_count'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Included in audio total</p>
            </x-filament::section>
            <x-filament::section>
                <p class="text-xs uppercase tracking-wider text-gray-500">Total used</p>
                <p class="text-2xl font-bold mt-1 text-primary-600">{{ $format($totalBytes) }}</p>
                @if ($usedPct !== null)
                    <p class="text-xs text-gray-500 mt-1">Host disk {{ $usedPct }}% full</p>
                @endif
            </x-filament::section>
        </div>

        <x-filament::section heading="Host disk">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Capacity</span>
                    <span class="font-semibold">{{ $format($stats['capacity_bytes']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Used</span>
                    <span class="font-semibold">{{ $format(($stats['capacity_bytes'] ?? 0) - ($stats['free_bytes'] ?? 0)) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Free</span>
                    <span class="font-semibold">{{ $format($stats['free_bytes']) }}</span>
                </div>
                @if ($usedPct !== null)
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mt-2">
                        <div class="h-full bg-primary-500 rounded-full" style="width: {{ $usedPct }}%"></div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section heading="Largest songs" description="Top 10 audio files by size — target these first when reducing storage cost">
            @if (empty($stats['largest_songs']))
                <p class="text-sm text-gray-500">No songs uploaded yet.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500 border-b border-gray-200">
                            <th class="pb-2">#</th>
                            <th class="pb-2">Title</th>
                            <th class="pb-2 text-right">Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['largest_songs'] as $i => $s)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="py-2 font-medium">
                                    <a href="{{ route('filament.admin.resources.songs.edit', ['record' => $s['id']]) }}" class="hover:underline">
                                        {{ $s['title'] }}
                                    </a>
                                </td>
                                <td class="py-2 text-right font-mono">{{ $s['size_mb'] }} MB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-filament::section>

        <div class="text-xs text-gray-400">
            Storage stats are cached for 5 minutes. Click Refresh to recompute.
        </div>
    </div>
</x-filament-panels::page>
