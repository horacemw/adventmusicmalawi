import { Link, router } from '@inertiajs/react';
import { ArrowLeft, ListMusic, Music2, Play, Trash2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import { formatDuration, usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';

interface Props {
    playlist: {
        id: number;
        name: string;
        description: string | null;
        cover: string | null;
        visibility: string;
        is_owner: boolean;
        created_at: string;
        songs: (SongPayload & { position: number })[];
    };
}

export default function PlaylistShow({ playlist }: Props) {
    const player = usePlayer();

    const playAll = () => {
        if (playlist.songs.length === 0) return;
        const [first, ...rest] = playlist.songs;
        player.play(first, rest);
    };

    const removeSong = (songId: number) => {
        if (!confirm('Remove this song from the playlist?')) return;
        router.delete(`/playlists/${playlist.id}/songs/${songId}`, { preserveScroll: true });
    };

    const deletePlaylist = () => {
        if (!confirm(`Delete "${playlist.name}"? This cannot be undone.`)) return;
        router.delete(`/playlists/${playlist.id}`);
    };

    return (
        <AppLayout title={playlist.name}>
            <div className="space-y-6">
                <Link href="/playlists" className="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-ink">
                    <ArrowLeft className="h-4 w-4" />
                    Back to playlists
                </Link>

                <header className="flex flex-col sm:flex-row items-start sm:items-end gap-6 pb-6 border-b border-slate-200">
                    <div className="h-40 w-40 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 shadow-card">
                        {playlist.cover ? (
                            <img src={playlist.cover} alt="" className="w-full h-full object-cover rounded-2xl" />
                        ) : (
                            <ListMusic className="h-16 w-16 text-white/70" />
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            {playlist.visibility} playlist · {playlist.songs.length} song{playlist.songs.length === 1 ? '' : 's'}
                        </p>
                        <h1 className="text-3xl md:text-4xl font-bold text-ink mb-2">{playlist.name}</h1>
                        {playlist.description && (
                            <p className="text-sm text-slate-600 max-w-xl">{playlist.description}</p>
                        )}
                        <p className="text-xs text-slate-500 mt-3">Created {playlist.created_at}</p>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                        <button
                            onClick={playAll}
                            disabled={playlist.songs.length === 0}
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            <Play className="h-4 w-4 translate-x-[1px]" />
                            Play
                        </button>
                        {playlist.is_owner && (
                            <button
                                onClick={deletePlaylist}
                                className="h-10 w-10 rounded-full text-rose-500 hover:bg-rose-50 flex items-center justify-center"
                                aria-label="Delete playlist"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        )}
                    </div>
                </header>

                {playlist.songs.length === 0 ? (
                    <div className="rounded-3xl bg-white border border-dashed border-slate-200 p-10 md:p-16 text-center">
                        <div className="mx-auto h-14 w-14 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                            <Music2 className="h-6 w-6" />
                        </div>
                        <h2 className="text-lg font-semibold text-ink mb-1">Playlist is empty</h2>
                        <p className="text-sm text-slate-500 max-w-md mx-auto mb-4">
                            Add songs by opening any song page and clicking "Add to playlist".
                        </p>
                        <Link
                            href="/discover"
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            Browse music
                        </Link>
                    </div>
                ) : (
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {playlist.songs.map((song, idx) => (
                            <li key={song.id} className="group">
                                <div className="flex items-center gap-3 px-4 py-3 hover:bg-slate-50">
                                    <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                                        {idx + 1}
                                    </span>
                                    <button
                                        onClick={() => player.play(song, playlist.songs.slice(idx + 1))}
                                        className="relative h-10 w-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden"
                                    >
                                        {song.artwork ? (
                                            <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                                        ) : (
                                            <Music2 className="h-4 w-4 text-white/70" />
                                        )}
                                        <span className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Play className="h-4 w-4 text-white translate-x-[1px]" />
                                        </span>
                                    </button>
                                    <button
                                        onClick={() => player.play(song, playlist.songs.slice(idx + 1))}
                                        className="flex-1 min-w-0 text-left"
                                    >
                                        <p className="text-sm font-medium text-ink truncate">{song.title}</p>
                                        <p className="text-xs text-slate-500 truncate">{song.artist}</p>
                                    </button>
                                    <span className="hidden sm:block text-xs tabular-nums text-slate-400 shrink-0">
                                        {formatDuration(song.duration)}
                                    </span>
                                    {playlist.is_owner && (
                                        <button
                                            onClick={() => removeSong(song.id)}
                                            className="p-2 text-slate-400 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                            aria-label="Remove song"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
