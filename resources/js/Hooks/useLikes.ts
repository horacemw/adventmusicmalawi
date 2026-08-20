import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import type { PageProps } from '@/types';

interface LikeableSong {
    id: number;
    slug: string;
}

/**
 * Optimistic like-toggling backed by /songs/{slug}/like.
 *
 * The initial like set comes from Inertia shared prop `likedSongIds`. We keep a
 * local Set for O(1) lookup and instant UI feedback; on API failure we roll back.
 * Anonymous users are redirected to /login.
 */
export function useLikes() {
    const { props } = usePage<PageProps>();
    const initial = useMemo(() => new Set(props.likedSongIds ?? []), [props.likedSongIds]);
    const [likedIds, setLikedIds] = useState<Set<number>>(initial);
    const user = props.auth?.user ?? null;

    // Keep local state in sync when Inertia navigates (shared props re-flow).
    useEffect(() => {
        setLikedIds(new Set(props.likedSongIds ?? []));
    }, [props.likedSongIds]);

    const isLiked = useCallback((songId: number) => likedIds.has(songId), [likedIds]);

    const toggleLike = useCallback(
        async (song: LikeableSong) => {
            if (!user) {
                router.visit('/login');
                return;
            }

            const wasLiked = likedIds.has(song.id);
            setLikedIds((prev) => {
                const next = new Set(prev);
                if (wasLiked) next.delete(song.id);
                else next.add(song.id);
                return next;
            });

            try {
                const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content;
                const res = await fetch(`/songs/${song.slug}/like`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                });
                if (!res.ok) throw new Error(`Like toggle failed: ${res.status}`);
                const data = (await res.json()) as { liked: boolean };
                setLikedIds((prev) => {
                    const next = new Set(prev);
                    if (data.liked) next.add(song.id);
                    else next.delete(song.id);
                    return next;
                });
            } catch {
                // Roll back optimistic update
                setLikedIds((prev) => {
                    const next = new Set(prev);
                    if (wasLiked) next.add(song.id);
                    else next.delete(song.id);
                    return next;
                });
            }
        },
        [user, likedIds],
    );

    return { isLiked, toggleLike, isAuthenticated: !!user };
}
