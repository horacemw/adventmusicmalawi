import clsx from 'clsx';
import { Heart, MoreHorizontal, Music2, Pause, Play, Repeat, Shuffle, SkipBack, SkipForward } from 'lucide-react';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';

interface Props {
    fallback: SongPayload | null;
}

export default function NowPlayingPanel({ fallback }: Props) {
    const player = usePlayer();
    const track = player.current ?? fallback;

    return (
        <aside className="hidden xl:flex flex-col w-80 shrink-0 gap-4">
            <div className="rounded-2xl bg-white border border-slate-200 shadow-card overflow-hidden">
                <div className="flex items-center justify-between px-5 pt-4">
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Now Playing
                    </p>
                    <button
                        type="button"
                        className="text-slate-400 hover:text-ink"
                        aria-label="More"
                    >
                        <MoreHorizontal className="h-4 w-4" />
                    </button>
                </div>

                <div className="p-5">
                    <div className="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center mb-4 shadow-card">
                        {track?.artwork ? (
                            <img
                                src={track.artwork}
                                alt=""
                                className="w-full h-full object-cover"
                            />
                        ) : (
                            <Music2 className="h-16 w-16 text-white/70" />
                        )}
                    </div>

                    <div className="flex items-start justify-between gap-3 mb-4">
                        <div className="min-w-0">
                            <p className="text-base font-semibold text-ink truncate">
                                {track?.title ?? 'Nothing playing'}
                            </p>
                            <p className="text-sm text-slate-500 truncate">
                                {track?.artist ?? '—'}
                            </p>
                        </div>
                        <button
                            type="button"
                            className="shrink-0 text-slate-400 hover:text-brand-600 transition-colors"
                            aria-label="Like"
                        >
                            <Heart className="h-5 w-5" />
                        </button>
                    </div>

                    <div className="flex items-center justify-between text-xs text-slate-500 mb-2 tabular-nums">
                        <span>{formatDuration(player.progress)}</span>
                        <span>{formatDuration(player.duration || track?.duration)}</span>
                    </div>
                    <div className="h-1 bg-slate-100 rounded-full overflow-hidden mb-5">
                        <div
                            className="h-full bg-brand-500 rounded-full transition-[width] duration-300"
                            style={{
                                width: `${
                                    ((player.progress /
                                        (player.duration || track?.duration || 1)) *
                                        100) || 0
                                }%`,
                            }}
                        />
                    </div>

                    <div className="flex items-center justify-between">
                        <button
                            type="button"
                            onClick={player.toggleShuffle}
                            className={clsx(
                                'p-2 rounded-full',
                                player.shuffle ? 'text-brand-600' : 'text-slate-400 hover:text-ink',
                            )}
                            aria-label="Shuffle"
                        >
                            <Shuffle className="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            onClick={player.previous}
                            className="p-2 text-slate-500 hover:text-ink"
                            aria-label="Previous"
                        >
                            <SkipBack className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                if (!player.current && track) {
                                    player.play(track);
                                } else {
                                    player.togglePlay();
                                }
                            }}
                            className="h-11 w-11 rounded-full bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shadow-card"
                            aria-label={player.isPlaying ? 'Pause' : 'Play'}
                        >
                            {player.isPlaying ? (
                                <Pause className="h-5 w-5" />
                            ) : (
                                <Play className="h-5 w-5 translate-x-[1px]" />
                            )}
                        </button>
                        <button
                            type="button"
                            onClick={player.next}
                            className="p-2 text-slate-500 hover:text-ink"
                            aria-label="Next"
                        >
                            <SkipForward className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={player.cycleRepeat}
                            className={clsx(
                                'p-2 rounded-full',
                                player.repeat !== 'off'
                                    ? 'text-brand-600'
                                    : 'text-slate-400 hover:text-ink',
                            )}
                            aria-label="Repeat"
                        >
                            <Repeat className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    );
}
