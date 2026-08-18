import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Sidebar from '@/Components/App/Sidebar';
import MobileNav from '@/Components/App/MobileNav';
import TopBar from '@/Components/App/TopBar';
import PlayerBar from '@/Components/App/PlayerBar';
import NowPlayingPanel from '@/Components/App/NowPlayingPanel';
import { PlayerProvider } from '@/Contexts/PlayerContext';
import { MobileNavProvider } from '@/Contexts/MobileNavContext';
import type { SongPayload } from '@/types';

interface Props {
    title?: string;
    children: ReactNode;
    rightPanel?: ReactNode;
    nowPlayingFallback?: SongPayload | null;
}

export default function AppLayout({
    title,
    children,
    rightPanel,
    nowPlayingFallback = null,
}: Props) {
    return (
        <MobileNavProvider>
            <PlayerProvider>
                {title && <Head title={title} />}
                <div className="min-h-screen bg-surface-subtle text-ink">
                    <Sidebar />
                    <MobileNav />
                    <div className="lg:pl-64 pb-24 sm:pb-28">
                        <div className="mx-auto max-w-[1800px] px-4 md:px-6 lg:px-8">
                            <TopBar />
                            <div className="flex gap-6">
                                <main className="flex-1 min-w-0 pb-6">{children}</main>
                                {rightPanel ?? (
                                    <NowPlayingPanel fallback={nowPlayingFallback} />
                                )}
                            </div>
                        </div>
                    </div>
                    <PlayerBar />
                </div>
            </PlayerProvider>
        </MobileNavProvider>
    );
}
