import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';
import type { SongPayload } from '@/types';

export type RepeatMode = 'off' | 'all' | 'one';

interface PlayerState {
    current: SongPayload | null;
    queue: SongPayload[];
    isPlaying: boolean;
    progress: number;
    duration: number;
    volume: number;
    muted: boolean;
    shuffle: boolean;
    repeat: RepeatMode;
}

interface PlayerContextValue extends PlayerState {
    play: (song: SongPayload, queue?: SongPayload[]) => void;
    togglePlay: () => void;
    pause: () => void;
    resume: () => void;
    next: () => void;
    previous: () => void;
    seek: (seconds: number) => void;
    setVolume: (v: number) => void;
    toggleMute: () => void;
    toggleShuffle: () => void;
    cycleRepeat: () => void;
    enqueue: (songs: SongPayload[]) => void;
    clearQueue: () => void;
    audioRef: React.RefObject<HTMLAudioElement | null>;
}

const PlayerContext = createContext<PlayerContextValue | null>(null);

export function PlayerProvider({ children }: { children: ReactNode }) {
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const [state, setState] = useState<PlayerState>({
        current: null,
        queue: [],
        isPlaying: false,
        progress: 0,
        duration: 0,
        volume: 0.8,
        muted: false,
        shuffle: false,
        repeat: 'off',
    });

    // Initialize audio element once
    useEffect(() => {
        if (typeof window === 'undefined') return;
        if (!audioRef.current) {
            audioRef.current = new Audio();
            audioRef.current.preload = 'metadata';
            audioRef.current.volume = state.volume;
        }
        const audio = audioRef.current;

        const onTimeUpdate = () =>
            setState((s) => ({ ...s, progress: audio.currentTime }));
        const onLoaded = () =>
            setState((s) => ({ ...s, duration: audio.duration || s.current?.duration || 0 }));
        const onEnded = () => handleEnded();
        const onPause = () => setState((s) => ({ ...s, isPlaying: false }));
        const onPlay = () => setState((s) => ({ ...s, isPlaying: true }));

        audio.addEventListener('timeupdate', onTimeUpdate);
        audio.addEventListener('loadedmetadata', onLoaded);
        audio.addEventListener('ended', onEnded);
        audio.addEventListener('pause', onPause);
        audio.addEventListener('play', onPlay);

        return () => {
            audio.removeEventListener('timeupdate', onTimeUpdate);
            audio.removeEventListener('loadedmetadata', onLoaded);
            audio.removeEventListener('ended', onEnded);
            audio.removeEventListener('pause', onPause);
            audio.removeEventListener('play', onPlay);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleEnded = useCallback(() => {
        setState((s) => {
            if (s.repeat === 'one' && s.current) {
                queueMicrotask(() => {
                    if (audioRef.current) {
                        audioRef.current.currentTime = 0;
                        audioRef.current.play().catch(() => {});
                    }
                });
                return s;
            }
            if (s.queue.length === 0) {
                return { ...s, isPlaying: false, progress: 0 };
            }
            const [nextSong, ...rest] = s.queue;
            queueMicrotask(() => {
                if (audioRef.current && nextSong.audio) {
                    audioRef.current.src = nextSong.audio;
                    audioRef.current.play().catch(() => {});
                }
            });
            return { ...s, current: nextSong, queue: rest, progress: 0 };
        });
    }, []);

    const play = useCallback((song: SongPayload, queue?: SongPayload[]) => {
        setState((s) => ({
            ...s,
            current: song,
            queue: queue ?? s.queue,
            progress: 0,
            duration: song.duration ?? 0,
        }));
        queueMicrotask(() => {
            if (!audioRef.current) return;
            if (song.audio) {
                audioRef.current.src = song.audio;
                audioRef.current.play().catch(() => {});
            } else {
                // No audio uploaded yet — simulate a play state for UI purposes.
                audioRef.current.removeAttribute('src');
                setState((s) => ({ ...s, isPlaying: true }));
            }
        });
    }, []);

    const togglePlay = useCallback(() => {
        setState((s) => {
            if (!s.current) return s;
            const audio = audioRef.current;
            if (!audio) return s;
            if (s.isPlaying) {
                audio.pause();
                return { ...s, isPlaying: false };
            }
            audio.play().catch(() => {});
            return { ...s, isPlaying: true };
        });
    }, []);

    const pause = useCallback(() => {
        audioRef.current?.pause();
        setState((s) => ({ ...s, isPlaying: false }));
    }, []);

    const resume = useCallback(() => {
        audioRef.current?.play().catch(() => {});
        setState((s) => ({ ...s, isPlaying: true }));
    }, []);

    const next = useCallback(() => handleEnded(), [handleEnded]);

    const previous = useCallback(() => {
        if (audioRef.current) {
            audioRef.current.currentTime = 0;
        }
        setState((s) => ({ ...s, progress: 0 }));
    }, []);

    const seek = useCallback((seconds: number) => {
        if (audioRef.current) {
            audioRef.current.currentTime = seconds;
        }
        setState((s) => ({ ...s, progress: seconds }));
    }, []);

    const setVolume = useCallback((v: number) => {
        if (audioRef.current) {
            audioRef.current.volume = v;
        }
        setState((s) => ({ ...s, volume: v, muted: v === 0 }));
    }, []);

    const toggleMute = useCallback(() => {
        setState((s) => {
            const nextMuted = !s.muted;
            if (audioRef.current) {
                audioRef.current.muted = nextMuted;
            }
            return { ...s, muted: nextMuted };
        });
    }, []);

    const toggleShuffle = useCallback(() => {
        setState((s) => ({ ...s, shuffle: !s.shuffle }));
    }, []);

    const cycleRepeat = useCallback(() => {
        setState((s) => ({
            ...s,
            repeat: s.repeat === 'off' ? 'all' : s.repeat === 'all' ? 'one' : 'off',
        }));
    }, []);

    const enqueue = useCallback((songs: SongPayload[]) => {
        setState((s) => ({ ...s, queue: [...s.queue, ...songs] }));
    }, []);

    const clearQueue = useCallback(() => {
        setState((s) => ({ ...s, queue: [] }));
    }, []);

    const value = useMemo<PlayerContextValue>(
        () => ({
            ...state,
            play,
            togglePlay,
            pause,
            resume,
            next,
            previous,
            seek,
            setVolume,
            toggleMute,
            toggleShuffle,
            cycleRepeat,
            enqueue,
            clearQueue,
            audioRef,
        }),
        [
            state,
            play,
            togglePlay,
            pause,
            resume,
            next,
            previous,
            seek,
            setVolume,
            toggleMute,
            toggleShuffle,
            cycleRepeat,
            enqueue,
            clearQueue,
        ],
    );

    return <PlayerContext.Provider value={value}>{children}</PlayerContext.Provider>;
}

export function usePlayer(): PlayerContextValue {
    const ctx = useContext(PlayerContext);
    if (!ctx) throw new Error('usePlayer must be used within PlayerProvider');
    return ctx;
}

export function formatDuration(seconds?: number | null): string {
    if (!seconds || Number.isNaN(seconds)) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}
