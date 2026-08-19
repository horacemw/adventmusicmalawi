import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import SectionHeader from '@/Components/Home/SectionHeader';
import SongList from '@/Components/Home/SongList';
import EntityCard from '@/Components/Discover/EntityCard';
import PoemCard from '@/Components/Discover/PoemCard';
import type { SongPayload } from '@/types';

interface AlbumCard { id: number; title: string; slug: string; artwork: string | null; artist: string; year: number | null; songs_count: number | null; }
interface GroupCard { id: number; name: string; slug: string; type: string; image: string | null; }
interface ArtistCard { id: number; name: string; slug: string; image: string | null; }
interface Chip { id: number; name: string; slug: string; icon: string | null; color: string | null; }
interface OccasionCard { id: number; name: string; slug: string; image: string | null; }
interface PoemPreview { id: number; title: string; slug: string; summary: string | null; image: string | null; author: string; category: string | null; }

interface Props {
    hero: { title: string; subtitle: string };
    chips: Chip[];
    newReleases: SongPayload[];
    topSongs: SongPayload[];
    trending: SongPayload[];
    albums: AlbumCard[];
    featuredGroups: GroupCard[];
    featuredArtists: ArtistCard[];
    occasions: OccasionCard[];
    poems: PoemPreview[];
}

export default function DiscoverIndex(props: Props) {
    return (
        <AppLayout title="Discover">
            <div className="space-y-8">
                <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-8 md:p-12">
                    <h1 className="text-3xl md:text-4xl font-bold mb-2">{props.hero.title}</h1>
                    <p className="text-white/80 max-w-2xl">{props.hero.subtitle}</p>
                </section>

                {props.chips.length > 0 && (
                    <section>
                        <SectionHeader title="Browse by category" />
                        <div className="flex flex-wrap gap-2">
                            {props.chips.map((c) => (
                                <Link
                                    key={c.id}
                                    href={`/songs?category=${c.slug}`}
                                    className="px-4 py-2 rounded-full bg-white border border-slate-200 hover:border-brand-400 hover:bg-brand-50 text-sm font-medium text-ink transition-colors"
                                >
                                    {c.name}
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                <SongList title="New Releases" items={props.newReleases} href="/songs?sort=newest" showRank={false} />

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <SongList title="Top Songs" items={props.topSongs} href="/top-100" showRank />
                    <SongList title="Trending" items={props.trending} href="/trending" showRank />
                </div>

                {props.albums.length > 0 && (
                    <section>
                        <SectionHeader title="Featured Albums" href="/albums" />
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            {props.albums.map((a) => (
                                <EntityCard
                                    key={a.id}
                                    href={`/albums/${a.slug}`}
                                    name={a.title}
                                    image={a.artwork}
                                    subtitle={a.artist}
                                />
                            ))}
                        </div>
                    </section>
                )}

                {props.featuredGroups.length > 0 && (
                    <section>
                        <SectionHeader title="Groups & Choirs" href="/groups" />
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            {props.featuredGroups.map((g) => (
                                <EntityCard
                                    key={g.id}
                                    href={`/groups/${g.slug}`}
                                    name={g.name}
                                    image={g.image}
                                    subtitle={g.type}
                                />
                            ))}
                        </div>
                    </section>
                )}

                {props.featuredArtists.length > 0 && (
                    <section>
                        <SectionHeader title="Artists" href="/artists" />
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            {props.featuredArtists.map((a) => (
                                <EntityCard
                                    key={a.id}
                                    href={`/artists/${a.slug}`}
                                    name={a.name}
                                    image={a.image}
                                    aspect="portrait"
                                />
                            ))}
                        </div>
                    </section>
                )}

                {props.poems.length > 0 && (
                    <section>
                        <SectionHeader title="Poems" href="/poems" />
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            {props.poems.map((p) => (
                                <PoemCard key={p.id} poem={p} />
                            ))}
                        </div>
                    </section>
                )}

                {props.occasions.length > 0 && (
                    <section>
                        <SectionHeader title="Occasions" href="/occasions" />
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            {props.occasions.map((o) => (
                                <Link
                                    key={o.id}
                                    href={`/occasions/${o.slug}`}
                                    className="group relative aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700"
                                >
                                    {o.image && <img src={o.image} alt="" className="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-90 transition-opacity" />}
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
                                    <p className="absolute bottom-3 left-4 right-4 text-white font-semibold text-sm md:text-base">{o.name}</p>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}
            </div>
        </AppLayout>
    );
}
