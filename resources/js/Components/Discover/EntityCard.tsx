import { Link } from '@inertiajs/react';
import { BadgeCheck, Music2 } from 'lucide-react';

interface Props {
    href: string;
    name: string;
    image?: string | null;
    subtitle?: string | null;
    verified?: boolean;
    aspect?: 'square' | 'portrait';
}

export default function EntityCard({ href, name, image, subtitle, verified, aspect = 'square' }: Props) {
    return (
        <Link
            href={href}
            className="group block rounded-2xl bg-white border border-slate-200 hover:border-brand-400 hover:shadow-card transition-all overflow-hidden"
        >
            <div className={`${aspect === 'portrait' ? 'aspect-[3/4]' : 'aspect-square'} bg-gradient-to-br from-brand-100 to-brand-300 relative`}>
                {image ? (
                    <img src={image} alt="" className="w-full h-full object-cover" />
                ) : (
                    <div className="w-full h-full flex items-center justify-center">
                        <Music2 className="h-10 w-10 text-brand-600/40" />
                    </div>
                )}
            </div>
            <div className="p-3">
                <div className="flex items-center gap-1.5 min-w-0">
                    <p className="text-sm font-semibold text-ink truncate">{name}</p>
                    {verified && <BadgeCheck className="h-3.5 w-3.5 text-brand-600 shrink-0" />}
                </div>
                {subtitle && <p className="text-xs text-slate-500 truncate mt-0.5">{subtitle}</p>}
            </div>
        </Link>
    );
}
