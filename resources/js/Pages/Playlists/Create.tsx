import { useForm, Link } from '@inertiajs/react';
import { ArrowLeft, ListMusic, Loader2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

export default function PlaylistsCreate() {
    const form = useForm({
        name: '',
        description: '',
        visibility: 'private' as 'private' | 'public' | 'unlisted',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/playlists');
    };

    return (
        <AppLayout title="New playlist">
            <div className="max-w-xl mx-auto space-y-6">
                <div>
                    <Link href="/playlists" className="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-ink mb-3">
                        <ArrowLeft className="h-4 w-4" />
                        Back to playlists
                    </Link>
                    <h1 className="text-2xl md:text-3xl font-bold text-ink">New playlist</h1>
                    <p className="text-sm text-slate-500 mt-1">Give it a name — you can add songs from any song page.</p>
                </div>

                <form onSubmit={submit} className="rounded-2xl bg-white border border-slate-200 shadow-card p-6 space-y-4">
                    <div className="flex items-center gap-4">
                        <div className="h-16 w-16 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0">
                            <ListMusic className="h-7 w-7 text-white/80" />
                        </div>
                        <div className="flex-1">
                            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Name</label>
                            <input
                                type="text"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                required
                                autoFocus
                                placeholder="Sabbath Morning Worship"
                                className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                            />
                            {form.errors.name && <p className="text-xs text-rose-600 mt-1">{form.errors.name}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Description (optional)</label>
                        <textarea
                            rows={3}
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            placeholder="Songs for peaceful Sabbath mornings…"
                            className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Visibility</label>
                        <div className="grid grid-cols-3 gap-2">
                            {(['private', 'unlisted', 'public'] as const).map((v) => (
                                <button
                                    key={v}
                                    type="button"
                                    onClick={() => form.setData('visibility', v)}
                                    className={`rounded-lg border px-3 py-2 text-xs font-semibold capitalize transition-colors ${
                                        form.data.visibility === v
                                            ? 'border-brand-500 bg-brand-50 text-brand-700'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300'
                                    }`}
                                >
                                    {v}
                                </button>
                            ))}
                        </div>
                        <p className="text-xs text-slate-500 mt-2">
                            {form.data.visibility === 'private' && 'Only you can see this playlist.'}
                            {form.data.visibility === 'unlisted' && 'Anyone with the link can view.'}
                            {form.data.visibility === 'public' && 'Discoverable across the platform.'}
                        </p>
                    </div>

                    <div className="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                        <Link href="/playlists" className="text-sm text-slate-500 hover:text-ink px-3 py-2">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={form.processing || !form.data.name}
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white text-sm font-semibold px-5 py-2"
                        >
                            {form.processing && <Loader2 className="h-4 w-4 animate-spin" />}
                            Create playlist
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
