import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import SongRow from '@/Components/Discover/SongRow';
import EntityCard from '@/Components/Discover/EntityCard';
import { BadgeCheck, Users } from 'lucide-react';
import type { SongPayload } from '@/types';

interface Member { name: string; role: string | null; voice_part: string | null; is_leader: boolean; }
interface GroupDetail {
    id: number;
    name: string;
    slug: string;
    type: string;
    description: string | null;
    image: string | null;
    cover: string | null;
    founded_year: number | null;
    is_verified: boolean;
    is_featured: boolean;
    social_links: Record<string, string>;
    church: { name: string; slug: string } | null;
    region: string | null;
    district: string | null;
    members: Member[];
}
interface AlbumCard { id: number; title: string; slug: string; artwork: string | null; year: number | null; }
interface Props { group: GroupDetail; songs: SongPayload[]; albums: AlbumCard[]; }

export default function GroupDetail({ group, songs, albums }: Props) {
    return (
        <AppLayout title={group.name}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-10 mb-8 relative overflow-hidden">
                {group.cover && <img src={group.cover} alt="" className="absolute inset-0 w-full h-full object-cover opacity-30" />}
                <div className="relative flex flex-col md:flex-row gap-6 items-start">
                    <div className="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden bg-black/20 shrink-0 border-4 border-white/20">
                        {group.image ? (
                            <img src={group.image} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <Users className="h-12 w-12 text-white/50" />
                            </div>
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs uppercase tracking-wider text-white/70 mb-1">{group.type.charAt(0).toUpperCase() + group.type.slice(1)}</p>
                        <h1 className="text-3xl md:text-5xl font-bold mb-1 break-words flex items-center gap-2">
                            {group.name}
                            {group.is_verified && <BadgeCheck className="h-6 w-6 text-brand-200" />}
                        </h1>
                        <p className="text-xs text-white/70 mt-3">
                            {group.church && <Link href={`/churches/${group.church.slug}`} className="hover:underline">{group.church.name}</Link>}
                            {group.church && (group.district || group.region) && ' · '}
                            {[group.district, group.region].filter(Boolean).join(' · ')}
                            {group.founded_year && ` · Since ${group.founded_year}`}
                        </p>
                    </div>
                </div>
            </section>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    {group.description && (
                        <section className="rounded-2xl bg-white border border-slate-200 p-5">
                            <p className="text-sm text-ink whitespace-pre-wrap">{group.description}</p>
                        </section>
                    )}

                    {songs.length > 0 && (
                        <section>
                            <h2 className="text-lg font-bold text-ink mb-3">Songs</h2>
                            <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                                {songs.map((s, i) => (
                                    <SongRow key={s.id} song={s} index={i} queue={songs} showRank={false} />
                                ))}
                            </ul>
                        </section>
                    )}

                    {albums.length > 0 && (
                        <section>
                            <h2 className="text-lg font-bold text-ink mb-3">Albums</h2>
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                {albums.map((a) => (
                                    <EntityCard key={a.id} href={`/albums/${a.slug}`} name={a.title} image={a.artwork} subtitle={a.year ? String(a.year) : null} />
                                ))}
                            </div>
                        </section>
                    )}
                </div>

                {group.members.length > 0 && (
                    <aside>
                        <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Members</h2>
                        <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                            {group.members.map((m, i) => (
                                <li key={i} className="px-4 py-3">
                                    <p className="text-sm font-medium text-ink flex items-center gap-1.5">
                                        {m.name}
                                        {m.is_leader && <span className="px-1.5 py-0.5 text-[10px] rounded bg-brand-100 text-brand-800 font-semibold uppercase">Lead</span>}
                                    </p>
                                    <p className="text-xs text-slate-500">
                                        {[m.role, m.voice_part].filter(Boolean).join(' · ') || '—'}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </aside>
                )}
            </div>

            {songs.length === 0 && albums.length === 0 && (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500 mt-6">
                    No published music yet.
                </div>
            )}
        </AppLayout>
    );
}
