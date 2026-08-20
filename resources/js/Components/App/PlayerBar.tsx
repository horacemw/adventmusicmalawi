import { Link } from '@inertiajs/react';
import clsx from 'clsx';
import {
    AlertCircle,
    Download,
    Heart,
    Loader2,
    ListMusic,
    Maximize2,
    Music2,
    Pause,
    Play,
    Repeat,
    Repeat1,
    Shuffle,
    SkipBack,
    SkipForward,
} from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';
import { useLikes } from '@/Hooks/useLikes';
import Scrubber from '@/Components/Player/Scrubber';
import VolumeControl from '@/Components/Player/VolumeControl';
import QueueSheet from '@/Components/Player/QueueSheet';
import FullscreenPlayer from '@/Components/Player/FullscreenPlayer';
import MobileMiniPlayer from '@/Components/Player/MobileMiniPlayer';

/**
 * Persistent playback bar shown at the bottom of every page.
 *
 * Desktop: three-column layout — currently playing / transport + scrubber / auxiliary controls.
 * Mobile:  handed off to <MobileMiniPlayer /> which docks above the mobile tab bar.
 *
 * The audio element itself lives in PlayerProvider, not here — so navigating
 * between pages doesn't remount the audio.
 */
export default function PlayerBar() {
    const player = usePlayer();
    const { isLiked, toggleLike } = useLikes();
    const hasSong = !!player.current;
    const isLoading = player.status === 'loading';
    const hasError = player.status === 'error';
    const canDownload = hasSong && (player.current?.allow_download ?? true);
    const liked = hasSong && player.current ? isLiked(player.current.id) : false;

    return (
        <>
            {/* Mobile: mini bar */}
            <MobileMiniPlayer />

            {/* Desktop bar (hidden on small screens) */}
            <div className="hidden sm:block fixed bottom-0 left-0 right-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur">
                <div className="mx-auto max-w-[1800px] px-4 md:px-6 h-20 grid grid-cols-[minmax(200px,1fr)_minmax(0,2fr)_minmax(200px,1fr)] items-center gap-4">
                    {/* LEFT: currently playing */}
                    <div className="flex items-center gap-3 min-w-0">
                        <button
                            type="button"
                            onClick={() => hasSong && player.setFullscreen(true)}
                            disabled={!hasSong}
                            className="h-12 w-12 rounded-lg overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white shrink-0 hover:opacity-90 transition-opacity disabled:cursor-default"
                            aria-label="Open now playing"
                        >
                            {player.current?.artwork ? (
                                <img src={player.current.artwork} alt="" className="w-full h-full object-cover" />
                            ) : (
                                <Music2 className="h-5 w-5" />
                            )}
                        </button>
                        <div className="min-w-0 flex-1">
                            {hasSong && player.current ? (
                                <>
                                    <Link
                                        href={`/songs/${player.current.slug}`}
                                        className="block text-sm font-semibold text-ink truncate hover:underline"
                                    >
                                        {player.current.title}
                                    </Link>
                                    <p className="text-xs text-slate-500 truncate">{player.current.artist}</p>
                                </>
                            ) : (
                                <>
                                    <p className="text-sm font-semibold text-slate-400">Nothing playing</p>
                                    <p className="text-xs text-slate-400">Pick a song to start</p>
                                </>
                            )}
                        </div>
                        <button
                            type="button"
                            onClick={() => player.current && toggleLike({ id: player.current.id, slug: player.current.slug })}
                            className={clsx(
                                'transition-colors shrink-0',
                                liked ? 'text-brand-600 hover:text-brand-700' : 'text-slate-400 hover:text-brand-600',
                            )}
                            aria-label={liked ? 'Unlike' : 'Like'}
                            aria-pressed={liked}
                            disabled={!hasSong}
                        >
                            <Heart className={clsx('h-4 w-4', liked && 'fill-current')} />
                        </button>
                    </div>

                    {/* CENTER: transport + scrubber */}
                    <div className="flex flex-col items-center gap-1 min-w-0 w-full">
                        <div className="flex items-center gap-2 md:gap-4">
                            <button
                                type="button"
                                onClick={player.toggleShuffle}
                                disabled={!hasSong}
                                className={clsx(
                                    'p-1.5 rounded-full transition-colors disabled:opacity-40',
                                    player.shuffle ? 'text-brand-600' : 'text-slate-500 hover:text-ink',
                                )}
                                aria-label={player.shuffle ? 'Shuffle on' : 'Shuffle off'}
                                aria-pressed={player.shuffle}
                            >
                                <Shuffle className="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                onClick={player.previous}
                                disabled={!hasSong}
                                className="p-1.5 text-slate-600 hover:text-ink disabled:opacity-40"
                                aria-label="Previous"
                            >
                                <SkipBack className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={hasError ? player.retry : player.togglePlay}
                                disabled={!hasSong}
                                className="h-10 w-10 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white flex items-center justify-center shadow-card transition-colors"
                                aria-label={hasError ? 'Retry' : player.isPlaying ? 'Pause' : 'Play'}
                            >
                                {hasError ? (
                                    <AlertCircle className="h-5 w-5" />
                                ) : isLoading ? (
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                ) : player.isPlaying ? (
                                    <Pause className="h-5 w-5" />
                                ) : (
                                    <Play className="h-5 w-5 translate-x-[1px]" />
                                )}
                            </button>
                            <button
                                type="button"
                                onClick={player.next}
                                disabled={!hasSong}
                                className="p-1.5 text-slate-600 hover:text-ink disabled:opacity-40"
                                aria-label="Next"
                            >
                                <SkipForward className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={player.cycleRepeat}
                                disabled={!hasSong}
                                className={clsx(
                                    'p-1.5 rounded-full transition-colors disabled:opacity-40',
                                    player.repeat !== 'off' ? 'text-brand-600' : 'text-slate-500 hover:text-ink',
                                )}
                                aria-label={`Repeat: ${player.repeat}`}
                            >
                                {player.repeat === 'one' ? <Repeat1 className="h-4 w-4" /> : <Repeat className="h-4 w-4" />}
                            </button>
                        </div>
                        <div className="w-full max-w-2xl px-2">
                            <Scrubber />
                        </div>
                    </div>

                    {/* RIGHT: aux controls */}
                    <div className="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            onClick={() => player.setQueueOpen(true)}
                            disabled={!hasSong}
                            className="text-slate-500 hover:text-ink disabled:opacity-40"
                            aria-label="Open queue"
                        >
                            <ListMusic className="h-4 w-4" />
                        </button>
                        <VolumeControl width={80} />
                        {canDownload && player.current?.slug && (
                            <a
                                href={`/download/song/${player.current.slug}`}
                                className="text-slate-500 hover:text-brand-700"
                                aria-label="Download"
                                title="Download"
                            >
                                <Download className="h-4 w-4" />
                            </a>
                        )}
                        <button
                            type="button"
                            onClick={() => hasSong && player.setFullscreen(true)}
                            disabled={!hasSong}
                            className="text-slate-500 hover:text-ink disabled:opacity-40"
                            aria-label="Expand"
                        >
                            <Maximize2 className="h-4 w-4" />
                        </button>
                    </div>
                </div>
                {hasError && (
                    <div className="border-t border-rose-100 bg-rose-50 px-4 py-1.5 text-xs text-rose-700 flex items-center gap-2">
                        <AlertCircle className="h-3.5 w-3.5" />
                        <span>{player.error ?? 'Playback failed.'}</span>
                        <button type="button" onClick={player.retry} className="ml-auto font-semibold hover:underline">
                            Retry
                        </button>
                    </div>
                )}
            </div>

            {player.queueOpen && <QueueSheet onClose={() => player.setQueueOpen(false)} />}
            {player.fullscreen && <FullscreenPlayer onClose={() => player.setFullscreen(false)} />}
        </>
    );
}
