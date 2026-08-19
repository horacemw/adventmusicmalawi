import { Link } from '@inertiajs/react';
import { Compass, Heart, Music2, Users2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

const values = [
    {
        icon: Music2,
        title: 'For the music',
        body: "Every song here is Adventist music from Malawi — hymns, worship, choir pieces, acapella ensembles, children's chorus, quartets, and everything in between.",
    },
    {
        icon: Users2,
        title: 'For the community',
        body: 'We connect church choirs, youth groups, music ministries, Pathfinders, Adventurers, and solo artists across all three regions of Malawi.',
    },
    {
        icon: Heart,
        title: 'Free for listeners',
        body: 'Streaming is 100% free. We keep the lights on through paid submissions from artists and groups, promotional packages, and event promotion — never listener subscriptions.',
    },
    {
        icon: Compass,
        title: 'Local first',
        body: "Built in Malawi, hosted for Malawi. Songs are organised by district, region, occasion, and mood so it's easy to find the right music for Sabbath, weddings, evangelism, or camp meetings.",
    },
];

export default function About() {
    return (
        <AppLayout title="About">
            <div className="max-w-3xl mx-auto space-y-10">
                <header className="text-center">
                    <p className="text-xs font-semibold uppercase tracking-widest text-brand-700 mb-2">
                        About
                    </p>
                    <h1 className="text-3xl md:text-5xl font-bold text-ink leading-tight mb-4">
                        Many Voices. One Adventist Sound.
                    </h1>
                    <p className="text-base md:text-lg text-slate-600 max-w-2xl mx-auto">
                        Malawi Adventist Music is the digital home for Seventh-day Adventist
                        music in Malawi — a single place to discover and enjoy songs from
                        churches, choirs, youth groups, and music ministries across the country.
                    </p>
                </header>

                <section className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {values.map((v) => {
                        const Icon = v.icon;
                        return (
                            <div key={v.title} className="rounded-2xl bg-white border border-slate-200 p-5 shadow-card">
                                <span className="h-10 w-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <h2 className="text-base font-semibold text-ink mb-1.5">{v.title}</h2>
                                <p className="text-sm text-slate-600">{v.body}</p>
                            </div>
                        );
                    })}
                </section>

                <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-8 md:p-10">
                    <h2 className="text-xl md:text-2xl font-bold mb-3">Are you an artist, choir, or group?</h2>
                    <p className="text-sm md:text-base text-brand-50/95 mb-5 max-w-xl">
                        Submit your music and share it with the whole country. There's a small
                        submission fee that keeps the platform free for listeners, and every
                        submission is reviewed by our moderators before it goes live.
                    </p>
                    <Link
                        href="/submit-music"
                        className="inline-flex items-center gap-2 rounded-full bg-white text-brand-700 hover:bg-brand-50 text-sm font-semibold px-5 py-2.5"
                    >
                        Submit your music
                    </Link>
                </section>

                <section className="rounded-2xl bg-white border border-slate-200 p-6">
                    <h2 className="text-lg font-semibold text-ink mb-3">The story behind the platform</h2>
                    <p className="text-sm text-slate-600 mb-3">
                        Malawi has an extraordinary wealth of Adventist music — some of it recorded
                        professionally, most of it captured on phones after a Sabbath service or a
                        youth rally. Until now, that music has lived in WhatsApp groups, on
                        memory cards, and on private YouTube uploads that are hard to find.
                    </p>
                    <p className="text-sm text-slate-600 mb-3">
                        We built this platform to give every one of those songs a permanent, easy-to-find
                        home. Whether you're preparing for a funeral, planning a wedding, choosing music
                        for a camp meeting, or just enjoying a quiet Sabbath afternoon, the right song
                        should be one search away.
                    </p>
                    <p className="text-sm text-slate-600">
                        We're still early, and the catalogue is still growing. Every submission from an
                        artist or a choir makes the platform more useful for everyone else. Thank you
                        for being part of it.
                    </p>
                </section>
            </div>
        </AppLayout>
    );
}
