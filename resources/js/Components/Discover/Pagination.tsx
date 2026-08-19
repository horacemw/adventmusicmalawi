import { Link } from '@inertiajs/react';

interface Props {
    meta: {
        current_page: number;
        last_page: number;
        total: number;
    };
    baseUrl?: string;
    queryString?: string;
}

export default function Pagination({ meta }: Props) {
    if (meta.last_page <= 1) return null;

    const page = meta.current_page;
    const last = meta.last_page;
    const params = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');

    const url = (n: number) => {
        params.set('page', String(n));
        return `?${params.toString()}`;
    };

    const nums: number[] = [];
    const from = Math.max(1, page - 2);
    const to = Math.min(last, page + 2);
    for (let i = from; i <= to; i++) nums.push(i);

    return (
        <nav className="mt-6 flex items-center justify-center gap-1 flex-wrap">
            <Link
                href={url(Math.max(1, page - 1))}
                preserveScroll={false}
                className={`px-3 py-1.5 rounded-lg text-sm font-medium ${page === 1 ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:bg-slate-100'}`}
            >
                Prev
            </Link>
            {from > 1 && (
                <>
                    <Link href={url(1)} className="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">1</Link>
                    {from > 2 && <span className="px-2 text-slate-400">…</span>}
                </>
            )}
            {nums.map((n) => (
                <Link
                    key={n}
                    href={url(n)}
                    className={`px-3 py-1.5 rounded-lg text-sm font-medium ${n === page ? 'bg-brand-600 text-white' : 'text-slate-700 hover:bg-slate-100'}`}
                >
                    {n}
                </Link>
            ))}
            {to < last && (
                <>
                    {to < last - 1 && <span className="px-2 text-slate-400">…</span>}
                    <Link href={url(last)} className="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">{last}</Link>
                </>
            )}
            <Link
                href={url(Math.min(last, page + 1))}
                preserveScroll={false}
                className={`px-3 py-1.5 rounded-lg text-sm font-medium ${page === last ? 'text-slate-300 pointer-events-none' : 'text-slate-700 hover:bg-slate-100'}`}
            >
                Next
            </Link>
        </nav>
    );
}
