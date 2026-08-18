import clsx from 'clsx';
import {
    Heart,
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
    Volume2,
    VolumeX,
} from 'lucide-react';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';

export default function PlayerBar() {
    const player = usePlayer();

    return (
        <div className="fixed bottom-0 left-0 right-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur">
            <div className="mx-auto max-w-[1800px] px-4 md:px-6 lg:px-8 flex items-center gap-4 h-20">
                {/* Track info */}
                <div className="flex items-center gap-3 w-64 shrink-0">
                    <div className="h-12 w-12 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white shadow-card">
                        {player.current?.artwork ? (
                            <img
                                src={player.current.artwork}
                                alt=""
                                className="h-12 w-12 rounded-lg object-cover"
                            />
                        ) : (
                            <Music2 className="h-5 w-5" />
                        )}
                    </div>
                    <div className="min-w-0">
                        <p className="text-sm font-semibold text-ink truncate">
                            {player.current?.title ?? 'Nothing playing'}
                        </p>
                        <p className="text-xs text-slate-500 truncate">
                            {player.current?.artist ?? 'Select a song to start listening'}
                        </p>
                    </div>
                    <button
                        type="button"
                        className="ml-auto text-slate-400 hover:text-brand-600 transition-colors"
                        aria-label="Like"
                    >
                        <Heart className="h-4 w-4" />
                    </button>
                </div>

                {/* Transport */}
                <div className="flex-1 flex flex-col items-center gap-1">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={player.toggleShuffle}
                            className={clsx(
                                'p-1.5 rounded-full transition-colors',
                                player.shuffle
                                    ? 'text-brand-600'
                                    : 'text-slate-400 hover:text-ink',
                            )}
                            aria-label="Shuffle"
                        >
                            <Shuffle className="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            onClick={player.previous}
                            className="p-1.5 text-slate-500 hover:text-ink"
                            aria-label="Previous"
                        >
                            <SkipBack className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={player.togglePlay}
                            disabled={!player.current}
                            className="h-10 w-10 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white flex items-center justify-center shadow-card transition-colors"
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
                            className="p-1.5 text-slate-500 hover:text-ink"
                            aria-label="Next"
                        >
                            <SkipForward className="h-5 w-5" />
                        </button>
                        <button
                            type="button"
                            onClick={player.cycleRepeat}
                            className={clsx(
                                'p-1.5 rounded-full transition-colors',
                                player.repeat !== 'off'
                                    ? 'text-brand-600'
                                    : 'text-slate-400 hover:text-ink',
                            )}
                            aria-label="Repeat"
                        >
                            {player.repeat === 'one' ? (
                                <Repeat1 className="h-4 w-4" />
                            ) : (
                                <Repeat className="h-4 w-4" />
                            )}
                        </button>
                    </div>
                    <div className="w-full flex items-center gap-2 max-w-xl">
                        <span className="text-[11px] tabular-nums text-slate-500 w-8 text-right">
                            {formatDuration(player.progress)}
                        </span>
                        <input
                            type="range"
                            min={0}
                            max={player.duration || player.current?.duration || 0}
                            value={player.progress}
                            onChange={(e) => player.seek(Number(e.target.value))}
                            className="player-range flex-1"
                            aria-label="Seek"
                        />
                        <span className="text-[11px] tabular-nums text-slate-500 w-8">
                            {formatDuration(player.duration || player.current?.duration)}
                        </span>
                    </div>
                </div>

                {/* Right controls */}
                <div className="hidden md:flex items-center gap-3 w-56 justify-end">
                    <button
                        type="button"
                        className="text-slate-400 hover:text-ink"
                        aria-label="Queue"
                    >
                        <ListMusic className="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        onClick={player.toggleMute}
                        className="text-slate-400 hover:text-ink"
                        aria-label="Mute"
                    >
                        {player.muted ? (
                            <VolumeX className="h-4 w-4" />
                        ) : (
                            <Volume2 className="h-4 w-4" />
                        )}
                    </button>
                    <input
                        type="range"
                        min={0}
                        max={1}
                        step={0.01}
                        value={player.muted ? 0 : player.volume}
                        onChange={(e) => player.setVolume(Number(e.target.value))}
                        className="player-range w-24"
                        aria-label="Volume"
                    />
                    <button
                        type="button"
                        className="text-slate-400 hover:text-ink"
                        aria-label="Expand"
                    >
                        <Maximize2 className="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    );
}
