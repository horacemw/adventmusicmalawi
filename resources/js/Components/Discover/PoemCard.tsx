import { Link } from '@inertiajs/react';
import { FileText } from 'lucide-react';

interface Poem {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    image: string | null;
    author: string;
    category?: string | null;
}

interface Props { poem: Poem; }

export default function PoemCard({ poem }: Props) {
    return (
        <Link
            href={`/poems/${poem.slug}`}
            className="group block rounded-2xl bg-white border border-slate-200 hover:border-brand-400 hover:shadow-card transition-all overflow-hidden"
        >
            <div className="aspect-[3/2] bg-gradient-to-br from-brand-100 to-brand-300 relative">
                {poem.image ? (
                    <img src={poem.image} alt="" className="w-full h-full object-cover" />
                ) : (
                    <div className="w-full h-full flex items-center justify-center">
                        <FileText className="h-10 w-10 text-brand-600/40" />
                    </div>
                )}
            </div>
            <div className="p-4">
                <p className="text-sm font-semibold text-ink line-clamp-2 mb-1">{poem.title}</p>
                <p className="text-xs text-slate-500 mb-2">{poem.author}</p>
                {poem.summary && <p className="text-xs text-slate-600 line-clamp-2">{poem.summary}</p>}
                {poem.category && (
                    <span className="mt-2 inline-block px-2 py-0.5 text-[10px] rounded-full bg-brand-50 text-brand-800 font-medium">{poem.category}</span>
                )}
            </div>
        </Link>
    );
}
