import { Music2, Play } from 'lucide-react';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';
import SectionHeader from './SectionHeader';

interface Props {
    title: string;
    items: SongPayload[];
    href?: string;
    showRank?: boolean;
}

export default function SongList({ title, items, href, showRank = true }: Props) {
    const player = usePlayer();

    if (items.length === 0) {
        return (
            <section>
                <SectionHeader title={title} href={href} />
                <div className="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                    Nothing here yet.
                </div>
            </section>
        );
    }

    return (
        <section>
            <SectionHeader title={title} href={href} />
            <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                {items.map((song, idx) => (
                    <li key={song.id}>
                        <button
                            onClick={() => player.play(song, items.filter((s) => s.id !== song.id))}
                            className="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group text-left"
                        >
                            {showRank ? (
                                <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                                    {song.rank ?? idx + 1}
                                </span>
                            ) : null}
                            <div className="relative h-10 w-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden">
                                {song.artwork ? (
                                    <img
                                        src={song.artwork}
                                        alt=""
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <Music2 className="h-4 w-4 text-white/70" />
                                )}
                                <span className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Play className="h-4 w-4 text-white translate-x-[1px]" />
                                </span>
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-ink truncate">
                                    {song.title}
                                </p>
                                <p className="text-xs text-slate-500 truncate">
                                    {song.artist}
                                </p>
                            </div>
                            <span className="hidden sm:block text-xs tabular-nums text-slate-400 shrink-0">
                                {formatDuration(song.duration)}
                            </span>
                        </button>
                    </li>
                ))}
            </ul>
        </section>
    );
}
