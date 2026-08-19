import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen bg-surface-subtle flex flex-col">
            <header className="border-b border-slate-200 bg-white/80 backdrop-blur">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center">
                    <Link href="/" className="inline-block">
                        <ApplicationLogo size="md" />
                    </Link>
                </div>
            </header>

            <main className="flex-1 flex items-start sm:items-center justify-center px-4 py-10">
                <div className="w-full sm:max-w-md">
                    <div className="bg-white shadow-card rounded-2xl border border-slate-200 px-6 py-8 sm:px-8">
                        {children}
                    </div>
                    <p className="mt-6 text-center text-xs text-slate-500">
                        Many Voices. One Adventist Sound.
                    </p>
                </div>
            </main>
        </div>
    );
}
