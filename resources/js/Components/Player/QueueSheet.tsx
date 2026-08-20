import { Music2, X } from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';
import { useBackButtonClose } from '@/Hooks/useBackButtonClose';
import PlayingIndicator from './PlayingIndicator';

interface Props {
    onClose: () => void;
}

/**
 * Slide-out queue panel that shows the currently playing song at the top,
 * the upcoming songs in the queue, and recently played history at the bottom.
 * Clicking an upcoming song jumps to it.
 */
export default function QueueSheet({ onClose }: Props) {
    const player = usePlayer();

    useBackButtonClose(onClose);

    return (
        <>
            {/* Backdrop */}
            <button
                type="button"
                onClick={onClose}
                aria-hidden
                className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm"
            />
            {/* Panel */}
            <aside
                className="fixed right-0 top-0 bottom-0 z-50 w-full sm:w-96 bg-white border-l border-slate-200 shadow-2xl flex flex-col mam-slide-up sm:mam-slide-up"
                aria-label="Play queue"
            >
                <header className="px-5 py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Play queue</p>
                        <p className="text-lg font-bold text-ink">Up next</p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="p-2 rounded-full hover:bg-slate-100 text-slate-500"
                        aria-label="Close queue"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </header>

                <div className="flex-1 overflow-y-auto pb-24">
                    {player.current && (
                        <section className="px-5 pt-4">
                            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Now playing</h3>
                            <QueueRow
                                artwork={player.current.artwork}
                                title={player.current.title}
                                artist={player.current.artist}
                                indicator={<PlayingIndicator isPlaying={player.isPlaying} />}
                            />
                        </section>
                    )}

                    {player.queue.length > 0 && (
                        <section className="px-5 pt-6">
                            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                                Next up ({player.queue.length})
                            </h3>
                            <ul className="space-y-1">
                                {player.queue.map((s, idx) => (
                                    <li key={`${s.id}-${idx}`}>
                                        <button
                                            type="button"
                                            onClick={() => player.playAt(idx)}
                                            className="w-full text-left"
                                        >
                                            <QueueRow
                                                artwork={s.artwork}
                                                title={s.title}
                                                artist={s.artist}
                                            />
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}

                    {player.history.length > 0 && (
                        <section className="px-5 pt-6">
                            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                                Recently played
                            </h3>
                            <ul className="space-y-1">
                                {player.history.slice(0, 20).map((s, idx) => (
                                    <li key={`${s.id}-h-${idx}`} className="opacity-60">
                                        <QueueRow
                                            artwork={s.artwork}
                                            title={s.title}
                                            artist={s.artist}
                                        />
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}

                    {!player.current && player.queue.length === 0 && (
                        <div className="p-8 text-center text-sm text-slate-500">
                            Nothing in the queue yet. Pick a song to get started.
                        </div>
                    )}
                </div>
            </aside>
        </>
    );
}

interface RowProps {
    artwork: string | null;
    title: string;
    artist: string;
    indicator?: React.ReactNode;
}

function QueueRow({ artwork, title, artist, indicator }: RowProps) {
    return (
        <div className="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-50 transition-colors">
            <div className="h-10 w-10 rounded-md overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0">
                {artwork ? (
                    <img src={artwork} alt="" className="w-full h-full object-cover" />
                ) : (
                    <Music2 className="h-4 w-4 text-white/70" />
                )}
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-medium text-ink truncate">{title}</p>
                <p className="text-xs text-slate-500 truncate">{artist}</p>
            </div>
            {indicator}
        </div>
    );
}
