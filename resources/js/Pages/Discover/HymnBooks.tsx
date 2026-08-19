import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import { BookOpen } from 'lucide-react';

interface Book {
    id: number;
    name: string;
    slug: string;
    language: string | null;
    publisher: string | null;
    published_year: number | null;
    cover: string | null;
    hymns_count: number;
}
interface Props { books: Book[]; }

export default function HymnBooks({ books }: Props) {
    return (
        <AppLayout title="Hymn Books">
            <PageHeader title="Hymn Books" subtitle="Traditional and modern hymnals with sung recordings." />
            {books.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    Hymn books coming soon.
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {books.map((b) => (
                        <article key={b.id} className="rounded-2xl bg-white border border-slate-200 p-4 flex gap-4 hover:border-brand-400 transition-colors">
                            <div className="w-20 h-28 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden">
                                {b.cover ? (
                                    <img src={b.cover} alt="" className="w-full h-full object-cover" />
                                ) : (
                                    <BookOpen className="h-6 w-6 text-white/70" />
                                )}
                            </div>
                            <div className="min-w-0">
                                <p className="text-sm font-semibold text-ink truncate">{b.name}</p>
                                {b.language && <p className="text-xs text-slate-500">{b.language}</p>}
                                {b.publisher && <p className="text-xs text-slate-500 truncate">{b.publisher}{b.published_year ? ` · ${b.published_year}` : ''}</p>}
                                <p className="text-xs text-brand-700 mt-1">{b.hymns_count} hymn{b.hymns_count === 1 ? '' : 's'}</p>
                            </div>
                        </article>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
