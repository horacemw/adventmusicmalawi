interface Props {
    title: string;
    subtitle?: string;
    count?: number | null;
}

export default function PageHeader({ title, subtitle, count }: Props) {
    return (
        <header className="mb-6">
            <div className="flex items-baseline flex-wrap gap-x-3 gap-y-1">
                <h1 className="text-2xl md:text-3xl font-bold text-ink">{title}</h1>
                {count !== undefined && count !== null && (
                    <span className="text-sm text-slate-500 tabular-nums">{count.toLocaleString()} total</span>
                )}
            </div>
            {subtitle && <p className="mt-1 text-sm text-slate-600 max-w-2xl">{subtitle}</p>}
        </header>
    );
}
