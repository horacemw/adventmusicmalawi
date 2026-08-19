import { Link } from '@inertiajs/react';
import { ListMusic, Plus } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import clsx from 'clsx';

interface PlaylistRow {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    cover: string | null;
    visibility: string;
    is_pinned: boolean;
    song_count: number;
    created_at: string;
}

export default function PlaylistsIndex({ playlists }: { playlists: PlaylistRow[] }) {
    return (
        <AppLayout title="My playlists">
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold text-ink">My playlists</h1>
                        <p className="text-sm text-slate-500 mt-1">{playlists.length} playlist{playlists.length === 1 ? '' : 's'}</p>
                    </div>
                    <Link
                        href="/playlists/new"
                        className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2"
                    >
                        <Plus className="h-4 w-4" />
                        New playlist
                    </Link>
                </div>

                {playlists.length === 0 ? (
                    <div className="rounded-3xl bg-white border border-slate-200 p-10 md:p-16 text-center shadow-card">
                        <div className="mx-auto h-14 w-14 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                            <ListMusic className="h-6 w-6" />
                        </div>
                        <h2 className="text-lg font-semibold text-ink mb-1">No playlists yet</h2>
                        <p className="text-sm text-slate-500 max-w-md mx-auto mb-6">
                            Start building your first playlist — collect songs for Sabbath, weddings, or your daily worship.
                        </p>
                        <Link
                            href="/playlists/new"
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            <Plus className="h-4 w-4" />
                            Create playlist
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        {playlists.map((p) => (
                            <Link
                                key={p.id}
                                href={`/playlists/${p.id}`}
                                className="rounded-2xl p-3 bg-white border border-slate-200 hover:shadow-card-hover hover:border-slate-300 transition-all"
                            >
                                <div className="relative aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 mb-3 flex items-center justify-center">
                                    {p.cover ? (
                                        <img src={p.cover} alt="" className="w-full h-full object-cover" />
                                    ) : (
                                        <ListMusic className="h-10 w-10 text-white/60" />
                                    )}
                                </div>
                                <p className="text-sm font-semibold text-ink line-clamp-1">{p.name}</p>
                                <div className="flex items-center gap-2 mt-1">
                                    <span className="text-xs text-slate-500">{p.song_count} song{p.song_count === 1 ? '' : 's'}</span>
                                    <span className={clsx(
                                        'text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded',
                                        p.visibility === 'public' && 'bg-brand-50 text-brand-700',
                                        p.visibility === 'private' && 'bg-slate-100 text-slate-600',
                                        p.visibility === 'unlisted' && 'bg-amber-50 text-amber-700',
                                    )}>
                                        {p.visibility}
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
