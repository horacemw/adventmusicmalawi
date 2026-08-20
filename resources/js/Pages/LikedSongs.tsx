import { Link } from '@inertiajs/react';
import { Heart, Music2, Pause, Play } from 'lucide-react';
import clsx from 'clsx';
import AppLayout from '@/Layouts/AppLayout';
import { usePlayer, formatDuration } from '@/Contexts/PlayerContext';
import { useLikes } from '@/Hooks/useLikes';
import type { SongPayload } from '@/types';

interface Props {
    songs: SongPayload[];
}

export default function LikedSongs({ songs }: Props) {
    const player = usePlayer();
    const { isLiked, toggleLike } = useLikes();

    const playAll = () => {
        if (songs.length === 0) return;
        const [first, ...rest] = songs;
        player.play(first, rest);
    };

    return (
        <AppLayout title="Liked Songs">
            <div className="space-y-6">
                <header className="flex flex-col md:flex-row md:items-end gap-5">
                    <div className="h-40 w-40 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-card shrink-0">
                        <Heart className="h-16 w-16 text-white fill-white" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="text-xs font-semibold uppercase tracking-wider text-brand-700">Your library</p>
                        <h1 className="text-3xl md:text-4xl font-bold text-ink mt-1">Liked Songs</h1>
                        <p className="text-sm text-slate-500 mt-2">
                            {songs.length} {songs.length === 1 ? 'song' : 'songs'}
                        </p>
                        {songs.length > 0 && (
                            <button
                                type="button"
                                onClick={playAll}
                                className="mt-4 inline-flex items-center gap-2 h-10 px-5 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-card transition-colors"
                            >
                                <Play className="h-4 w-4 translate-x-[1px]" />
                                Play all
                            </button>
                        )}
                    </div>
                </header>

                {songs.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center">
                        <Heart className="h-10 w-10 text-slate-300 mx-auto" />
                        <p className="mt-3 text-sm font-semibold text-ink">No liked songs yet</p>
                        <p className="text-xs text-slate-500 mt-1">
                            Tap the heart next to any song to save it here.
                        </p>
                        <Link
                            href="/songs"
                            className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:underline"
                        >
                            Browse songs
                        </Link>
                    </div>
                ) : (
                    <div className="rounded-2xl bg-white border border-slate-200 overflow-hidden">
                        <ul className="divide-y divide-slate-100">
                            {songs.map((song, idx) => {
                                const isCurrent = player.current?.id === song.id;
                                const isPlayingThis = isCurrent && player.isPlaying;
                                const liked = isLiked(song.id);
                                return (
                                    <li key={song.id} className="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 group">
                                        <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                                            {idx + 1}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                if (isCurrent) {
                                                    player.togglePlay();
                                                } else {
                                                    const rest = songs.slice(idx + 1);
                                                    player.play(song, rest);
                                                }
                                            }}
                                            className="relative h-11 w-11 rounded-lg overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0"
                                            aria-label={isPlayingThis ? 'Pause' : 'Play'}
                                        >
                                            {song.artwork ? (
                                                <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                                            ) : (
                                                <Music2 className="h-4 w-4 text-white/70" />
                                            )}
                                            <span
                                                className={clsx(
                                                    'absolute inset-0 bg-black/40 flex items-center justify-center transition-opacity',
                                                    isPlayingThis ? 'opacity-100' : 'opacity-0 group-hover:opacity-100',
                                                )}
                                            >
                                                {isPlayingThis ? (
                                                    <Pause className="h-4 w-4 text-white" />
                                                ) : (
                                                    <Play className="h-4 w-4 text-white translate-x-[1px]" />
                                                )}
                                            </span>
                                        </button>
                                        <div className="flex-1 min-w-0">
                                            <Link
                                                href={`/songs/${song.slug}`}
                                                className={clsx(
                                                    'block text-sm font-medium truncate hover:underline',
                                                    isCurrent ? 'text-brand-700' : 'text-ink',
                                                )}
                                            >
                                                {song.title}
                                            </Link>
                                            <p className="text-xs text-slate-500 truncate">{song.artist}</p>
                                        </div>
                                        <span className="hidden sm:inline text-xs tabular-nums text-slate-400 w-12 text-right">
                                            {formatDuration(song.duration ?? 0)}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => toggleLike({ id: song.id, slug: song.slug })}
                                            className={clsx(
                                                'p-2 rounded-full transition-colors',
                                                liked ? 'text-brand-600 hover:text-brand-700' : 'text-slate-400 hover:text-brand-600',
                                            )}
                                            aria-label={liked ? 'Unlike' : 'Like'}
                                            aria-pressed={liked}
                                        >
                                            <Heart className={clsx('h-4 w-4', liked && 'fill-current')} />
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
