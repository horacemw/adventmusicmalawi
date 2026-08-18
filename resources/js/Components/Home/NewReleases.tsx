import { Music2, Play } from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';
import SectionHeader from './SectionHeader';

export default function NewReleases({ items }: { items: SongPayload[] }) {
    const player = usePlayer();

    if (items.length === 0) {
        return (
            <section>
                <SectionHeader title="New Releases" href="/songs" />
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No releases yet — check back soon.
                </div>
            </section>
        );
    }

    return (
        <section>
            <SectionHeader title="New Releases" href="/songs" />
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                {items.map((song) => (
                    <button
                        key={song.id}
                        onClick={() => player.play(song, items.filter((s) => s.id !== song.id))}
                        className="group text-left rounded-2xl p-3 bg-white border border-slate-200 hover:shadow-card-hover hover:border-slate-300 transition-all"
                    >
                        <div className="relative aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 mb-3">
                            {song.artwork ? (
                                <img
                                    src={song.artwork}
                                    alt=""
                                    className="w-full h-full object-cover"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center">
                                    <Music2 className="h-10 w-10 text-white/60" />
                                </div>
                            )}
                            <span className="absolute right-2 bottom-2 h-9 w-9 rounded-full bg-white text-brand-600 shadow-card flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <Play className="h-4 w-4 translate-x-[1px]" />
                            </span>
                        </div>
                        <p className="text-sm font-semibold text-ink line-clamp-1">
                            {song.title}
                        </p>
                        <p className="text-xs text-slate-500 line-clamp-1 mt-0.5">
                            {song.artist}
                        </p>
                    </button>
                ))}
            </div>
        </section>
    );
}
