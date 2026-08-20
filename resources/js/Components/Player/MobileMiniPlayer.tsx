import { AlertCircle, ChevronUp, Loader2, Music2, Pause, Play, SkipForward } from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';

/**
 * Compact bottom bar on mobile. Tap the artwork or title to open the fullscreen sheet.
 * A thin progress line at the top gives at-a-glance playback progress.
 */
export default function MobileMiniPlayer() {
    const player = usePlayer();
    const totalDuration = player.duration || player.current?.duration || 0;
    const percent = totalDuration > 0 ? Math.min(100, (player.currentTime / totalDuration) * 100) : 0;
    const isLoading = player.status === 'loading';
    const hasError = player.status === 'error';

    if (!player.current) return null;

    return (
        <div className="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 shadow-lg">
            {/* Thin playback line */}
            <div className="h-1 bg-slate-100">
                <div
                    className="h-full bg-brand-500 transition-[width] duration-100 ease-linear"
                    style={{ width: `${percent}%` }}
                />
            </div>

            <div className="flex items-center gap-3 px-3 py-2">
                <button
                    type="button"
                    onClick={() => player.setFullscreen(true)}
                    className="flex items-center gap-3 min-w-0 flex-1"
                    aria-label="Open now playing"
                >
                    <div className="h-11 w-11 rounded-lg overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0">
                        {player.current.artwork ? (
                            <img src={player.current.artwork} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <Music2 className="h-4 w-4 text-white/80" />
                        )}
                    </div>
                    <div className="min-w-0 flex-1 text-left">
                        <p className="text-sm font-semibold text-ink truncate">{player.current.title}</p>
                        <p className="text-xs text-slate-500 truncate">{player.current.artist}</p>
                    </div>
                    <ChevronUp className="h-4 w-4 text-slate-400 shrink-0" />
                </button>

                <button
                    type="button"
                    onClick={hasError ? player.retry : player.togglePlay}
                    className="h-10 w-10 rounded-full bg-brand-600 text-white flex items-center justify-center shrink-0"
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
                    className="p-2 text-slate-500 hover:text-ink shrink-0"
                    aria-label="Next"
                >
                    <SkipForward className="h-5 w-5" />
                </button>
            </div>
        </div>
    );
}
