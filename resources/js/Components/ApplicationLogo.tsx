import type { HTMLAttributes } from 'react';
import { Music2 } from 'lucide-react';

interface Props extends HTMLAttributes<HTMLSpanElement> {
    /** When true, show just the icon tile (no wordmark). */
    iconOnly?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const SIZES = {
    sm: { tile: 'h-8 w-8', icon: 'h-4 w-4', kicker: 'text-[10px]', name: 'text-xs' },
    md: { tile: 'h-9 w-9', icon: 'h-5 w-5', kicker: 'text-[11px]', name: 'text-sm' },
    lg: { tile: 'h-12 w-12', icon: 'h-6 w-6', kicker: 'text-xs', name: 'text-base' },
};

export default function ApplicationLogo({
    iconOnly = false,
    size = 'md',
    className = '',
    ...props
}: Props) {
    const s = SIZES[size];
    return (
        <span className={`inline-flex items-center gap-2.5 ${className}`} {...props}>
            <span
                className={`relative ${s.tile} rounded-xl bg-brand-600 flex items-center justify-center shadow-card`}
            >
                <Music2 className={`${s.icon} text-white`} />
            </span>
            {!iconOnly && (
                <span className="flex flex-col leading-tight">
                    <span
                        className={`${s.kicker} font-semibold uppercase tracking-widest text-brand-700`}
                    >
                        Malawi
                    </span>
                    <span className={`${s.name} font-semibold text-ink`}>
                        Adventist Music
                    </span>
                </span>
            )}
        </span>
    );
}
