import { Download, Music2, Pause, Play } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';
import PlayingIndicator from '@/Components/Player/PlayingIndicator';

interface Props {
    song: SongPayload;
    index: number;
    queue: SongPayload[];
    showRank?: boolean;
    allowDownload?: boolean;
}

export default function SongRow({ song, index, queue, showRank = true, allowDownload = true }: Props) {
    const player = usePlayer();
    const isActive = player.current?.id === song.id;
    const isPlaying = isActive && player.isPlaying;

    const play = () => {
        if (isActive) {
            player.togglePlay();
            return;
        }
        player.play(song, queue.filter((s) => s.id !== song.id));
    };

    return (
        <li className="group">
            <div className={`w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors ${isActive ? 'bg-brand-50/50' : ''}`}>
                {showRank && (
                    <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                        {isActive ? (
                            <PlayingIndicator isPlaying={isPlaying} className="mx-auto" />
                        ) : (
                            song.rank ?? index + 1
                        )}
                    </span>
                )}
                <button
                    onClick={play}
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
                <Link href={`/songs/${song.slug}`} className="flex-1 min-w-0 text-left">
                    <p className={`text-sm font-medium truncate hover:underline ${isActive ? 'text-brand-700' : 'text-ink'}`}>{song.title}</p>
                    <p className="text-xs text-slate-500 truncate">{song.artist}</p>
                </Link>
                <span className="hidden sm:block text-xs tabular-nums text-slate-400 shrink-0">
                    {formatDuration(song.duration)}
                </span>
                {allowDownload && (
                    <a
                        href={`/download/song/${song.slug}`}
                        className="p-1.5 text-slate-400 hover:text-brand-700 opacity-0 group-hover:opacity-100 transition-opacity"
                        aria-label={`Download ${song.title}`}
                        title="Download"
                    >
                        <Download className="h-4 w-4" />
                    </a>
                )}
            </div>
        </li>
    );
}
