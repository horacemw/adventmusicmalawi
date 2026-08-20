import { Link } from '@inertiajs/react';
import clsx from 'clsx';
import {
    AlertCircle,
    ChevronDown,
    Download,
    Heart,
    ListMusic,
    Loader2,
    Music2,
    Pause,
    Play,
    Repeat,
    Repeat1,
    Shuffle,
    SkipBack,
    SkipForward,
} from 'lucide-react';
import { useEffect } from 'react';
import { usePlayer } from '@/Contexts/PlayerContext';
import { useBackButtonClose } from '@/Hooks/useBackButtonClose';
import { useLikes } from '@/Hooks/useLikes';
import Scrubber from './Scrubber';
import VolumeControl from './VolumeControl';
import QueueSheet from './QueueSheet';

interface Props {
    onClose: () => void;
}

/**
 * Full-screen "Now Playing" experience. Used on mobile when the user taps the
 * mini-player, and on desktop when they click the expand button.
 */
export default function FullscreenPlayer({ onClose }: Props) {
    const player = usePlayer();
    const { isLiked, toggleLike, isAuthenticated } = useLikes();
    const song = player.current;
    const isLoading = player.status === 'loading';
    const hasError = player.status === 'error';
    const canDownload = Boolean(song) && (song?.allow_download ?? true);
    const liked = song ? isLiked(song.id) : false;

    // Close on Escape
    useEffect(() => {
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [onClose]);

    // Phone/browser back button should close this overlay, not leave the site.
    useBackButtonClose(onClose);

    if (!song) return null;

    return (
        <div className="fixed inset-0 z-50 bg-white mam-slide-up flex flex-col overflow-hidden">
            {/* Ambient artwork blur behind */}
            {song.artwork && (
                <div
                    aria-hidden
                    className="absolute inset-0 opacity-30 blur-3xl scale-110"
                    style={{ backgroundImage: `url(${song.artwork})`, backgroundSize: 'cover', backgroundPosition: 'center' }}
                />
            )}
            <div className="relative flex-1 overflow-y-auto flex flex-col">
                <header className="px-5 pt-5 pb-2 flex items-center justify-between">
                    <button
                        type="button"
                        onClick={onClose}
                        className="p-2 -ml-2 rounded-full hover:bg-slate-100 text-slate-700"
                        aria-label="Close"
                    >
                        <ChevronDown className="h-6 w-6" />
                    </button>
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Now playing</p>
                    <button
                        type="button"
                        onClick={() => player.setQueueOpen(true)}
                        className="p-2 -mr-2 rounded-full hover:bg-slate-100 text-slate-700"
                        aria-label="Open queue"
                    >
                        <ListMusic className="h-5 w-5" />
                    </button>
                </header>

                <div className="flex-1 flex flex-col items-center justify-center px-6 py-4 gap-6 max-w-md mx-auto w-full">
                    {/* Artwork */}
                    <div className="w-full max-w-sm aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 shadow-2xl">
                        {song.artwork ? (
                            <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <Music2 className="h-24 w-24 text-white/70" />
                            </div>
                        )}
                    </div>

                    {/* Meta */}
                    <div className="w-full flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            <Link
                                href={`/songs/${song.slug}`}
                                onClick={onClose}
                                className="block text-2xl font-bold text-ink truncate hover:underline"
                            >
                                {song.title}
                            </Link>
                            <p className="text-base text-slate-600 truncate">{song.artist}</p>
                        </div>
                        {isAuthenticated && (
                            <button
                                type="button"
                                onClick={() => song && toggleLike({ id: song.id, slug: song.slug })}
                                className={clsx(
                                    'shrink-0 transition-colors',
                                    liked ? 'text-brand-600 hover:text-brand-700' : 'text-slate-400 hover:text-brand-600',
                                )}
                                aria-label={liked ? 'Unlike' : 'Like'}
                                aria-pressed={liked}
                            >
                                <Heart className="h-6 w-6" fill={liked ? 'currentColor' : 'none'} />
                            </button>
                        )}
                    </div>

                    {/* Scrubber */}
                    <div className="w-full">
                        <Scrubber labelSize="sm" />
                    </div>

                    {/* Transport */}
                    <div className="w-full flex items-center justify-between px-2">
                        <button
                            type="button"
                            onClick={player.toggleShuffle}
                            className={clsx(
                                'p-2 rounded-full transition-colors',
                                player.shuffle ? 'text-brand-600' : 'text-slate-500 hover:text-ink',
                            )}
                            aria-label="Shuffle"
                            aria-pressed={player.shuffle}
                        >
                            <Shuffle className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={player.previous}
                            className="p-2 text-slate-700 hover:text-ink"
                            aria-label="Previous"
                        >
                            <SkipBack className="h-7 w-7" />
                        </button>
                        <button
                            type="button"
                            onClick={hasError ? player.retry : player.togglePlay}
                            className="h-16 w-16 rounded-full bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shadow-lg"
                            aria-label={hasError ? 'Retry' : player.isPlaying ? 'Pause' : 'Play'}
                        >
                            {hasError ? (
                                <AlertCircle className="h-8 w-8" />
                            ) : isLoading ? (
                                <Loader2 className="h-8 w-8 animate-spin" />
                            ) : player.isPlaying ? (
                                <Pause className="h-8 w-8" />
                            ) : (
                                <Play className="h-8 w-8 translate-x-[2px]" />
                            )}
                        </button>
                        <button
                            type="button"
                            onClick={player.next}
                            className="p-2 text-slate-700 hover:text-ink"
                            aria-label="Next"
                        >
                            <SkipForward className="h-7 w-7" />
                        </button>
                        <button
                            type="button"
                            onClick={player.cycleRepeat}
                            className={clsx(
                                'p-2 rounded-full transition-colors',
                                player.repeat !== 'off' ? 'text-brand-600' : 'text-slate-500 hover:text-ink',
                            )}
                            aria-label={`Repeat: ${player.repeat}`}
                        >
                            {player.repeat === 'one' ? <Repeat1 className="h-5 w-5" /> : <Repeat className="h-5 w-5" />}
                        </button>
                    </div>

                    {/* Auxiliary row */}
                    <div className="w-full flex items-center justify-between pt-2">
                        <VolumeControl width={120} />
                        {canDownload && (
                            <a
                                href={`/download/song/${song.slug}`}
                                className="flex items-center gap-1.5 text-sm text-slate-600 hover:text-brand-700"
                            >
                                <Download className="h-4 w-4" />
                                Download
                            </a>
                        )}
                    </div>

                    {hasError && (
                        <div className="w-full rounded-xl bg-rose-50 border border-rose-100 px-4 py-3 text-sm text-rose-700 flex items-center gap-2">
                            <AlertCircle className="h-4 w-4 shrink-0" />
                            <span className="flex-1">{player.error ?? 'Playback failed.'}</span>
                            <button type="button" onClick={player.retry} className="font-semibold hover:underline">Retry</button>
                        </div>
                    )}
                </div>
            </div>

            {player.queueOpen && <QueueSheet onClose={() => player.setQueueOpen(false)} />}
        </div>
    );
}
