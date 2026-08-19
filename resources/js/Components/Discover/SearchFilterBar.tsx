import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Props {
    initialQuery?: string;
    placeholder?: string;
    routeName: string;
    extraParams?: Record<string, string>;
}

export default function SearchFilterBar({ initialQuery = '', placeholder = 'Search…', routeName, extraParams = {} }: Props) {
    const [q, setQ] = useState(initialQuery);
    const debounced = useRef<number | null>(null);

    useEffect(() => {
        if (debounced.current) window.clearTimeout(debounced.current);
        debounced.current = window.setTimeout(() => {
            if (q === initialQuery) return;
            router.get(routeName, { ...extraParams, q: q || undefined }, {
                preserveState: true, preserveScroll: true, replace: true,
            });
        }, 350);
        return () => {
            if (debounced.current) window.clearTimeout(debounced.current);
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [q]);

    return (
        <div className="mb-4 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <input
                type="search"
                value={q}
                onChange={(e) => setQ(e.target.value)}
                placeholder={placeholder}
                className="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-200 bg-white text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
            />
        </div>
    );
}
