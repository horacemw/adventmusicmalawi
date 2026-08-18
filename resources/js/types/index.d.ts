export interface User {
    id: number;
    name: string;
    username?: string | null;
    email: string;
    email_verified_at?: string;
    avatar_path?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
};

export interface SongPayload {
    id: number;
    title: string;
    slug: string;
    artist: string;
    artwork: string | null;
    duration: number | null;
    audio: string | null;
    streams: number;
    likes: number;
    rank?: number | null;
}

export interface CategoryChip {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    color: string | null;
}

export interface OccasionCard {
    id: number;
    name: string;
    slug: string;
    image: string | null;
}

export interface FeaturedGroup {
    id: number;
    name: string;
    slug: string;
    type: string;
    image: string | null;
}

export interface HomeProps {
    hero: {
        title: string;
        subtitle: string;
        cta: { label: string; href: string };
    };
    chips: CategoryChip[];
    newReleases: SongPayload[];
    topSongs: SongPayload[];
    occasions: OccasionCard[];
    trending: SongPayload[];
    featuredGroups: FeaturedGroup[];
    nowPlaying: SongPayload | null;
}
