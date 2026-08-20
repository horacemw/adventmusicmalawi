import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Sidebar from '@/Components/App/Sidebar';
import MobileNav from '@/Components/App/MobileNav';
import TopBar from '@/Components/App/TopBar';
import PlayerBar from '@/Components/App/PlayerBar';
import FlashToaster from '@/Components/App/FlashToaster';
import { PlayerProvider } from '@/Contexts/PlayerContext';
import { MobileNavProvider } from '@/Contexts/MobileNavContext';
import type { SongPayload } from '@/types';

interface Props {
    title?: string;
    children: ReactNode;
    /** @deprecated retained for backwards compatibility; no longer rendered */
    rightPanel?: ReactNode;
    /** @deprecated retained for backwards compatibility; no longer rendered */
    nowPlayingFallback?: SongPayload | null;
}

export default function AppLayout({ title, children }: Props) {
    return (
        <MobileNavProvider>
            <PlayerProvider>
                {title && <Head title={title} />}
                <div className="min-h-screen bg-surface-subtle text-ink">
                    <Sidebar />
                    <MobileNav />
                    <div className="lg:pl-64 pb-28 sm:pb-24">
                        <div className="mx-auto max-w-[1800px] px-4 md:px-6 lg:px-8">
                            <TopBar />
                            <main className="pb-6">{children}</main>
                        </div>
                    </div>
                    <PlayerBar />
                    <FlashToaster />
                </div>
            </PlayerProvider>
        </MobileNavProvider>
    );
}
