import { Link } from '@inertiajs/react';
import { ArrowRight, Music2 } from 'lucide-react';

interface Props {
    title: string;
    subtitle: string;
    cta: { label: string; href: string };
}

export default function HeroCard({ title, subtitle, cta }: Props) {
    return (
        <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 text-white shadow-card">
            <div className="absolute inset-0 opacity-20 pointer-events-none">
                <svg
                    className="absolute right-0 top-0 h-full"
                    viewBox="0 0 400 400"
                    fill="none"
                    aria-hidden
                >
                    <circle cx="320" cy="80" r="200" fill="white" fillOpacity="0.08" />
                    <circle cx="360" cy="300" r="120" fill="white" fillOpacity="0.06" />
                </svg>
            </div>

            <div className="relative flex flex-col md:flex-row items-center gap-6 p-6 md:p-10">
                <div className="flex-1 max-w-xl">
                    <div className="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur px-3 py-1 text-xs font-medium mb-4">
                        <Music2 className="h-3.5 w-3.5" />
                        <span>Many Voices. One Adventist Sound.</span>
                    </div>
                    <h1 className="text-2xl md:text-4xl font-bold leading-tight tracking-tight mb-3">
                        {title}
                    </h1>
                    <p className="text-brand-50/90 text-sm md:text-base mb-6 max-w-md">
                        {subtitle}
                    </p>
                    <Link
                        href={cta.href}
                        className="inline-flex items-center gap-2 rounded-full bg-white text-brand-700 px-5 py-2.5 text-sm font-semibold hover:bg-brand-50 transition-colors"
                    >
                        {cta.label}
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>

                {/* Decorative stack of album squares */}
                <div className="relative hidden md:block h-40 w-56 shrink-0">
                    <div className="absolute right-0 top-2 h-32 w-32 rounded-2xl bg-white/15 backdrop-blur-sm rotate-6" />
                    <div className="absolute right-8 top-6 h-32 w-32 rounded-2xl bg-white/20 backdrop-blur-sm -rotate-3" />
                    <div className="absolute right-16 top-0 h-32 w-32 rounded-2xl bg-white/25 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Music2 className="h-12 w-12 text-white/80" />
                    </div>
                </div>
            </div>
        </section>
    );
}
