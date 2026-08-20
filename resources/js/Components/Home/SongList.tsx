import { Download, Music2, Pause, Play } from 'lucide-react';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';
import SectionHeader from './SectionHeader';
import PlayingIndicator from '@/Components/Player/PlayingIndicator';

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

    const play = (song: SongPayload) => {
        if (player.current?.id === song.id) {
            player.togglePlay();
            return;
        }
        player.play(song, items.filter((s) => s.id !== song.id));
    };

    return (
        <section>
            <SectionHeader title={title} href={href} />
            <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                {items.map((song, idx) => {
                    const isActive = player.current?.id === song.id;
                    const isPlaying = isActive && player.isPlaying;
                    return (
                        <li key={song.id} className="group">
                            <div className={`w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors ${isActive ? 'bg-brand-50/50' : ''}`}>
                                {showRank ? (
                                    <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                                        {isActive ? (
                                            <PlayingIndicator isPlaying={isPlaying} className="mx-auto" />
                                        ) : (
                                            song.rank ?? idx + 1
                                        )}
                                    </span>
                                ) : null}
                                <button
                                    onClick={() => play(song)}
                                    className="relative h-10 w-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden"
                                    aria-label={isPlaying ? `Pause ${song.title}` : `Play ${song.title}`}
                                >
                                    {song.artwork ? (
                                        <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                                    ) : (
                                        <Music2 className="h-4 w-4 text-white/70" />
                                    )}
                                    <span className={`absolute inset-0 bg-black/40 flex items-center justify-center transition-opacity ${isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'}`}>
                                        {isPlaying ? (
                                            <Pause className="h-4 w-4 text-white" />
                                        ) : (
                                            <Play className="h-4 w-4 text-white translate-x-[1px]" />
                                        )}
                                    </span>
                                </button>
                                <button
                                    onClick={() => play(song)}
                                    className="flex-1 min-w-0 text-left"
                                >
                                    <p className={`text-sm font-medium truncate ${isActive ? 'text-brand-700' : 'text-ink'}`}>{song.title}</p>
                                    <p className="text-xs text-slate-500 truncate">{song.artist}</p>
                                </button>
                                <span className="hidden sm:block text-xs tabular-nums text-slate-400 shrink-0">
                                    {formatDuration(song.duration)}
                                </span>
                                <a
                                    href={`/download/song/${song.slug}`}
                                    className="p-1.5 text-slate-400 hover:text-brand-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                    aria-label={`Download ${song.title}`}
                                    title="Download"
                                >
                                    <Download className="h-4 w-4" />
                                </a>
                            </div>
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}
