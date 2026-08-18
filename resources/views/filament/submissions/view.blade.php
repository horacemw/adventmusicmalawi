@php
    /** @var \App\Models\Submission $submission */
    /** @var \App\Models\SubmissionFile|null $audio */
    /** @var \App\Models\SubmissionFile|null $artwork */
    /** @var \App\Models\SubmissionFile|null $permission */
    $audioUrl = $audio ? \Illuminate\Support\Facades\Storage::disk('public')->url($audio->storage_path) : null;
    $artworkUrl = $artwork ? \Illuminate\Support\Facades\Storage::disk('public')->url($artwork->storage_path) : null;
    $permissionUrl = $permission ? \Illuminate\Support\Facades\Storage::disk('public')->url($permission->storage_path) : null;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <x-filament::section heading="Song">
                    <div class="grid gap-4 sm:grid-cols-2 text-sm">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Title</div>
                            <div class="font-semibold">{{ $submission->song_title }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Artist / Group / Church</div>
                            <div>{{ collect([$submission->artist_name, $submission->group_name, $submission->church_name])->filter()->join(' · ') ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Album</div>
                            <div>{{ $submission->album_title ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Release year</div>
                            <div>{{ $submission->release_year ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Language / Genre</div>
                            <div>{{ collect([optional($submission->language)->name, optional($submission->genre)->name])->filter()->join(' · ') ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Region / District</div>
                            <div>{{ collect([optional($submission->region)->name, optional($submission->district)->name])->filter()->join(' · ') ?: '—' }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Categories</div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($submission->categories as $c)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-primary-50 text-primary-700">{{ $c->name }}</span>
                                @empty <span>—</span> @endforelse
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Occasions</div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($submission->occasions as $o)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-50 text-amber-800">{{ $o->name }}</span>
                                @empty <span>—</span> @endforelse
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Moods</div>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($submission->moods as $m)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-sky-50 text-sky-700">{{ $m->name }}</span>
                                @empty <span>—</span> @endforelse
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Description</div>
                            <div>{{ $submission->description ?: '—' }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section heading="Audio">
                    @if ($audio)
                        <div class="space-y-2">
                            <audio controls class="w-full" src="{{ $audioUrl }}"></audio>
                            <div class="text-xs text-gray-500">{{ $audio->original_name }} · {{ $audio->mime_type }} · {{ number_format(($audio->size_bytes ?? 0) / 1024 / 1024, 1) }} MB</div>
                            @if ($audio->checksum_sha256)
                                <div class="text-[11px] font-mono text-gray-400 truncate">sha256: {{ $audio->checksum_sha256 }}</div>
                            @endif
                        </div>
                    @else
                        <div class="text-sm text-gray-500">No audio uploaded.</div>
                    @endif
                </x-filament::section>

                <x-filament::section heading="Copyright & permission">
                    <div class="grid gap-3 sm:grid-cols-2 text-sm">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Copyright owner</div>
                            <div>{{ $submission->copyright_owner ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Rights holder</div>
                            <div>{{ $submission->rights_holder ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Permission status</div>
                            <div>{{ ucwords(str_replace('_', ' ', (string) $submission->permission_status)) ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Confirmations</div>
                            <div class="flex flex-wrap gap-1">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $submission->owner_confirmation ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $submission->owner_confirmation ? '✓' : '✗' }} Owner
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $submission->platform_distribution_permission ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $submission->platform_distribution_permission ? '✓' : '✗' }} Distribution
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $submission->accuracy_confirmation ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $submission->accuracy_confirmation ? '✓' : '✗' }} Accuracy
                                </span>
                            </div>
                        </div>
                        @if ($permission)
                            <div class="sm:col-span-2">
                                <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Permission document</div>
                                <a href="{{ $permissionUrl }}" target="_blank" rel="noopener" class="text-primary-600 hover:underline text-sm">{{ $permission->original_name }}</a>
                            </div>
                        @endif
                        @if ($submission->copyright_notes)
                            <div class="sm:col-span-2">
                                <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Notes from submitter</div>
                                <div class="whitespace-pre-line">{{ $submission->copyright_notes }}</div>
                            </div>
                        @endif
                    </div>
                </x-filament::section>

                @if ($submission->reviews->isNotEmpty())
                    <x-filament::section heading="Review history">
                        <ol class="space-y-3 text-sm">
                            @foreach ($submission->reviews->sortByDesc('created_at') as $review)
                                <li class="border-l-2 border-primary-500 pl-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">{{ optional($review->reviewer)->name ?? 'System' }}</span>
                                        <span class="px-1.5 py-0.5 text-[11px] rounded-full bg-gray-100 text-gray-700">{{ str_replace('_', ' ', $review->action) }}</span>
                                        <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($review->reason)
                                        <div class="mt-1 text-gray-700">Reason: {{ $review->reason }}</div>
                                    @endif
                                    @if ($review->notes)
                                        <div class="mt-1 text-gray-700">{{ $review->notes }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </x-filament::section>
                @endif
            </div>

            <div class="space-y-4">
                <x-filament::section heading="Status">
                    <div class="text-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="font-semibold">{{ ucwords(str_replace('_', ' ', $submission->status)) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Reference</span>
                            <span class="font-mono text-xs">{{ $submission->reference }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Submitted</span>
                            <span>{{ $submission->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if ($submission->reviewed_at)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Reviewed</span>
                                <span>{{ $submission->reviewed_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($submission->song_id)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Song</span>
                                <span class="font-mono text-xs">#{{ $submission->song_id }}</span>
                            </div>
                        @endif
                    </div>
                </x-filament::section>

                <x-filament::section heading="Submitter">
                    <div class="text-sm space-y-2">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Name</div>
                            <div>{{ $submission->submitter_name }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Email</div>
                            <div class="break-all">{{ $submission->submitter_email }}</div>
                        </div>
                        @if ($submission->submitter_phone)
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Phone</div>
                                <div>{{ $submission->submitter_phone }}</div>
                            </div>
                        @endif
                        <div class="pt-1 text-xs text-gray-500">
                            Account: {{ optional($submission->user)->email ?? '—' }}
                        </div>
                    </div>
                </x-filament::section>

                @if ($artwork)
                    <x-filament::section heading="Artwork">
                        <img src="{{ $artworkUrl }}" alt="Artwork" class="w-full rounded-lg" />
                        <div class="mt-2 text-xs text-gray-500">{{ $artwork->original_name }}</div>
                    </x-filament::section>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
