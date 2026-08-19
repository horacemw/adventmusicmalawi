import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PoemCard from '@/Components/Discover/PoemCard';
import { Download, Eye, FileText } from 'lucide-react';

interface PoemDetail {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    body: string;
    image: string | null;
    document: string | null;
    allow_download: boolean;
    is_featured: boolean;
    view_count: number;
    like_count: number;
    published_at: string | null;
    author: string;
    artist: { name: string; slug: string; image: string | null } | null;
    church: { name: string; slug: string } | null;
    category: { name: string; slug: string } | null;
    language: string | null;
}
interface RelatedPoem {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    image: string | null;
    author: string;
}
interface Props { poem: PoemDetail; related: RelatedPoem[]; }

export default function PoemDetailPage({ poem, related }: Props) {
    return (
        <AppLayout title={poem.title}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-12 mb-8 relative overflow-hidden">
                {poem.image && <img src={poem.image} alt="" className="absolute inset-0 w-full h-full object-cover opacity-25" />}
                <div className="relative">
                    <p className="text-xs uppercase tracking-wider text-white/70 mb-2">Poem</p>
                    <h1 className="text-3xl md:text-5xl font-bold mb-3 break-words max-w-3xl">{poem.title}</h1>
                    <p className="text-white/90 text-sm md:text-base mb-1">
                        {poem.artist ? (
                            <Link href={`/artists/${poem.artist.slug}`} className="hover:underline">{poem.author}</Link>
                        ) : poem.church ? (
                            <Link href={`/churches/${poem.church.slug}`} className="hover:underline">{poem.author}</Link>
                        ) : (
                            poem.author
                        )}
                    </p>
                    <p className="text-xs text-white/60">
                        {poem.published_at}
                        {poem.language && ` · ${poem.language}`}
                        {' · '}<Eye className="inline h-3 w-3" /> {poem.view_count.toLocaleString()} views
                    </p>
                    {poem.allow_download && poem.document && (
                        <a
                            href={poem.document}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-brand-700 font-semibold hover:bg-brand-50 transition-colors"
                        >
                            <Download className="h-4 w-4" /> Download document
                        </a>
                    )}
                </div>
            </section>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <article className="lg:col-span-2 rounded-2xl bg-white border border-slate-200 p-6 md:p-10">
                    {poem.summary && (
                        <p className="text-sm italic text-slate-600 mb-6 pb-6 border-b border-slate-100">{poem.summary}</p>
                    )}
                    <div className="prose prose-slate max-w-none">
                        <pre className="whitespace-pre-wrap font-serif text-base md:text-lg leading-relaxed text-ink !bg-transparent !p-0 !m-0">{poem.body}</pre>
                    </div>
                </article>

                <aside className="space-y-4">
                    <section className="rounded-2xl bg-white border border-slate-200 p-5 space-y-3 text-sm">
                        {poem.category && (
                            <div>
                                <p className="text-xs uppercase tracking-wider text-slate-500">Category</p>
                                <Link href={`/poems?category=${poem.category.slug}`} className="text-brand-700 hover:underline">{poem.category.name}</Link>
                            </div>
                        )}
                        {poem.church && (
                            <div>
                                <p className="text-xs uppercase tracking-wider text-slate-500">Church</p>
                                <Link href={`/churches/${poem.church.slug}`} className="text-brand-700 hover:underline">{poem.church.name}</Link>
                            </div>
                        )}
                        {poem.document && !poem.allow_download && (
                            <p className="text-xs text-slate-500 flex items-center gap-1.5"><FileText className="h-3.5 w-3.5" /> Original document available on request</p>
                        )}
                    </section>
                </aside>
            </div>

            {related.length > 0 && (
                <section className="mt-10">
                    <h2 className="text-lg font-bold text-ink mb-3">More poems</h2>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        {related.map((r) => (
                            <PoemCard key={r.id} poem={{ ...r, category: null }} />
                        ))}
                    </div>
                </section>
            )}
        </AppLayout>
    );
}
