import AppLayout from '@/Layouts/AppLayout';
import CategoryChips from '@/Components/Home/CategoryChips';
import FeaturedGroups from '@/Components/Home/FeaturedGroups';
import HeroCard from '@/Components/Home/HeroCard';
import NewReleases from '@/Components/Home/NewReleases';
import OccasionsGrid from '@/Components/Home/OccasionsGrid';
import SongList from '@/Components/Home/SongList';
import type { HomeProps } from '@/types';

export default function Home(props: HomeProps) {
    return (
        <AppLayout title="Home" nowPlayingFallback={props.nowPlaying}>
            <div className="space-y-8">
                <HeroCard {...props.hero} />
                <CategoryChips chips={props.chips} />
                <NewReleases items={props.newReleases} />

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <SongList
                        title="Top Songs"
                        items={props.topSongs}
                        href="/top-100"
                        showRank
                    />
                    <OccasionsGrid items={props.occasions} />
                </div>

                <FeaturedGroups items={props.featuredGroups} />

                <SongList
                    title="Trending This Week"
                    items={props.trending}
                    href="/trending"
                    showRank
                />
            </div>
        </AppLayout>
    );
}
