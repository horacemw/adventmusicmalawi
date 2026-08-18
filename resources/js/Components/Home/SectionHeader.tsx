import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface Props {
    title: string;
    href?: string;
    action?: string;
}

export default function SectionHeader({ title, href, action = 'See all' }: Props) {
    return (
        <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg md:text-xl font-semibold text-ink">{title}</h2>
            {href && (
                <Link
                    href={href}
                    className="text-xs md:text-sm font-medium text-slate-500 hover:text-brand-700 flex items-center gap-0.5"
                >
                    {action}
                    <ChevronRight className="h-3.5 w-3.5" />
                </Link>
            )}
        </div>
    );
}
