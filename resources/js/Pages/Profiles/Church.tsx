import AppLayout from '@/Layouts/AppLayout';
import SongRow from '@/Components/Discover/SongRow';
import EntityCard from '@/Components/Discover/EntityCard';
import { BadgeCheck, Church as ChurchIcon } from 'lucide-react';
import type { SongPayload } from '@/types';

interface ChurchDetail {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    cover: string | null;
    address: string | null;
    is_verified: boolean;
    region: string | null;
    district: string | null;
}
interface GroupCard { id: number; name: string; slug: string; type: string; image: string | null; }
interface ArtistCard { id: number; name: string; slug: string; image: string | null; }
interface Props { church: ChurchDetail; groups: GroupCard[]; artists: ArtistCard[]; songs: SongPayload[]; }

export default function ChurchDetail({ church, groups, artists, songs }: Props) {
    return (
        <AppLayout title={church.name}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-10 mb-8 relative overflow-hidden">
                {church.cover && <img src={church.cover} alt="" className="absolute inset-0 w-full h-full object-cover opacity-30" />}
                <div className="relative flex flex-col md:flex-row gap-6 items-start">
                    <div className="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden bg-black/20 shrink-0 border-4 border-white/20">
                        {church.image ? (
                            <img src={church.image} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <ChurchIcon className="h-12 w-12 text-white/50" />
                            </div>
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs uppercase tracking-wider text-white/70 mb-1">Church</p>
                        <h1 className="text-3xl md:text-5xl font-bold mb-1 break-words flex items-center gap-2">
                            {church.name}
                            {church.is_verified && <BadgeCheck className="h-6 w-6 text-brand-200" />}
                        </h1>
                        <p className="text-xs text-white/70 mt-3">
                            {[church.district, church.region].filter(Boolean).join(' · ')}
                            {church.address && ` · ${church.address}`}
                        </p>
                    </div>
                </div>
            </section>

            {church.description && (
                <section className="rounded-2xl bg-white border border-slate-200 p-5 mb-6">
                    <p className="text-sm text-ink whitespace-pre-wrap">{church.description}</p>
                </section>
            )}

            {groups.length > 0 && (
                <section className="mb-8">
                    <h2 className="text-lg font-bold text-ink mb-3">Music groups</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        {groups.map((g) => (
                            <EntityCard key={g.id} href={`/groups/${g.slug}`} name={g.name} image={g.image} subtitle={g.type} />
                        ))}
                    </div>
                </section>
            )}

            {artists.length > 0 && (
                <section className="mb-8">
                    <h2 className="text-lg font-bold text-ink mb-3">Artists</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {artists.map((a) => (
                            <EntityCard key={a.id} href={`/artists/${a.slug}`} name={a.name} image={a.image} aspect="portrait" />
                        ))}
                    </div>
                </section>
            )}

            {songs.length > 0 && (
                <section>
                    <h2 className="text-lg font-bold text-ink mb-3">Recent songs</h2>
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {songs.map((s, i) => (
                            <SongRow key={s.id} song={s} index={i} queue={songs} showRank={false} />
                        ))}
                    </ul>
                </section>
            )}

            {groups.length === 0 && artists.length === 0 && songs.length === 0 && (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No music yet from this church.
                </div>
            )}
        </AppLayout>
    );
}
