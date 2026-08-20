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
export type PlayerStatus = 'idle' | 'loading' | 'playing' | 'paused' | 'error';

interface PersistedState {
    current: SongPayload | null;
    queue: SongPayload[];
    history: SongPayload[];
    originalQueue: SongPayload[] | null;
    volume: number;
    muted: boolean;
    shuffle: boolean;
    repeat: RepeatMode;
    lastPosition: number;
}

interface PlayerContextValue {
    // reactive state
    current: SongPayload | null;
    queue: SongPayload[];
    history: SongPayload[];
    isPlaying: boolean;
    status: PlayerStatus;
    currentTime: number;
    duration: number;
    volume: number;
    muted: boolean;
    shuffle: boolean;
    repeat: RepeatMode;
    error: string | null;
    fullscreen: boolean;
    queueOpen: boolean;

    // actions
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
    playAt: (index: number) => void;
    setFullscreen: (open: boolean) => void;
    setQueueOpen: (open: boolean) => void;
    retry: () => void;
}

const PlayerContext = createContext<PlayerContextValue | null>(null);
const PERSIST_KEY = 'mam.player.v2';

function loadPersisted(): Partial<PersistedState> {
    if (typeof window === 'undefined') return {};
    try {
        const raw = window.localStorage.getItem(PERSIST_KEY);
        if (!raw) return {};
        return JSON.parse(raw) as PersistedState;
    } catch {
        return {};
    }
}

function persist(state: PersistedState) {
    if (typeof window === 'undefined') return;
    try {
        window.localStorage.setItem(PERSIST_KEY, JSON.stringify(state));
    } catch {
        // localStorage full or unavailable — no-op
    }
}

function shuffleArray<T>(arr: T[]): T[] {
    const out = [...arr];
    for (let i = out.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [out[i], out[j]] = [out[j], out[i]];
    }
    return out;
}

export function PlayerProvider({ children }: { children: ReactNode }) {
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const rafRef = useRef<number | null>(null);
    const persistedInit = loadPersisted();

    // reactive state
    const [current, setCurrent] = useState<SongPayload | null>(persistedInit.current ?? null);
    const [queue, setQueue] = useState<SongPayload[]>(persistedInit.queue ?? []);
    const [history, setHistory] = useState<SongPayload[]>(persistedInit.history ?? []);
    const [originalQueue, setOriginalQueue] = useState<SongPayload[] | null>(persistedInit.originalQueue ?? null);
    const [status, setStatus] = useState<PlayerStatus>('idle');
    const [currentTime, setCurrentTime] = useState<number>(persistedInit.lastPosition ?? 0);
    const [duration, setDuration] = useState<number>(0);
    const [volume, setVolumeState] = useState<number>(persistedInit.volume ?? 0.8);
    const [muted, setMuted] = useState<boolean>(persistedInit.muted ?? false);
    const [shuffle, setShuffle] = useState<boolean>(persistedInit.shuffle ?? false);
    const [repeat, setRepeat] = useState<RepeatMode>(persistedInit.repeat ?? 'off');
    const [error, setError] = useState<string | null>(null);
    const [fullscreen, setFullscreen] = useState<boolean>(false);
    const [queueOpen, setQueueOpen] = useState<boolean>(false);

    const isPlaying = status === 'playing';

    // Analytics: one report per (song, load) — reset when audio.src changes.
    const analyticsRef = useRef<{ songId: number | null; startedAt: number; secondsPlayed: number; reported: boolean }>({
        songId: null,
        startedAt: 0,
        secondsPlayed: 0,
        reported: false,
    });

    // Initialize the single Audio element once
    useEffect(() => {
        if (typeof window === 'undefined') return;
        if (audioRef.current) return;

        const audio = new Audio();
        audio.preload = 'metadata';
        audio.volume = volume;
        audio.muted = muted;
        audioRef.current = audio;

        // If we had a persisted "current" song, prime the src so play/resume works
        // after a hard refresh. Autoplay is not attempted — browsers block it without
        // a user gesture — but the position and metadata will render.
        if (current?.audio) {
            audio.src = current.audio;
            if (persistedInit.lastPosition && persistedInit.lastPosition > 0) {
                const applyTime = () => {
                    audio.currentTime = persistedInit.lastPosition ?? 0;
                    audio.removeEventListener('loadedmetadata', applyTime);
                };
                audio.addEventListener('loadedmetadata', applyTime);
            }
            setStatus('paused');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Persist relevant state whenever it changes
    useEffect(() => {
        persist({
            current,
            queue,
            history,
            originalQueue,
            volume,
            muted,
            shuffle,
            repeat,
            lastPosition: currentTime,
        });
    }, [current, queue, history, originalQueue, volume, muted, shuffle, repeat, currentTime]);

    // Post analytics when a song has been listened to. Fires at most once per song load.
    const reportStream = useCallback((completed: boolean) => {
        const info = analyticsRef.current;
        if (info.reported || info.songId == null) return;
        // Only count if the listener actually heard a chunk. Backend applies the
        // full "counted" logic (min duration, unique-session, etc); this is just
        // a cheap client-side gate to avoid spamming the endpoint on skips.
        if (info.secondsPlayed < 5) return;
        info.reported = true;

        const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content;
        fetch('/api/streams', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            body: JSON.stringify({
                song_id: info.songId,
                duration_played: Math.round(info.secondsPlayed),
                completed,
            }),
        }).catch(() => {
            // Silent — analytics failures shouldn't disturb playback UX
        });
    }, []);

    // Wire up audio element event listeners once.
    useEffect(() => {
        const audio = audioRef.current;
        if (!audio) return;

        const onLoadedMeta = () => {
            setDuration(audio.duration || 0);
            if (status === 'loading' && Number.isFinite(audio.duration)) {
                // Ready — but wait for canplay before switching to playing
            }
        };
        const onCanPlay = () => {
            setError(null);
        };
        const onPlay = () => {
            setStatus('playing');
            analyticsRef.current.startedAt = performance.now();
        };
        const onPause = () => {
            // Track time listened between play → pause
            if (analyticsRef.current.startedAt > 0) {
                const delta = (performance.now() - analyticsRef.current.startedAt) / 1000;
                analyticsRef.current.secondsPlayed += delta;
                analyticsRef.current.startedAt = 0;
            }
            setStatus((prev) => (prev === 'playing' ? 'paused' : prev));
        };
        const onEnded = () => {
            if (analyticsRef.current.startedAt > 0) {
                const delta = (performance.now() - analyticsRef.current.startedAt) / 1000;
                analyticsRef.current.secondsPlayed += delta;
                analyticsRef.current.startedAt = 0;
            }
            reportStream(true);
            handleTrackEnded();
        };
        const onError = () => {
            setStatus('error');
            setError('Playback failed. The audio file may be missing or unsupported.');
        };
        const onWaiting = () => {
            setStatus((prev) => (prev === 'playing' ? 'loading' : prev));
        };
        const onPlaying = () => {
            setStatus('playing');
        };
        const onTimeUpdate = () => {
            // Fallback in case rAF is throttled (tab backgrounded)
            setCurrentTime(audio.currentTime);
        };

        audio.addEventListener('loadedmetadata', onLoadedMeta);
        audio.addEventListener('canplay', onCanPlay);
        audio.addEventListener('play', onPlay);
        audio.addEventListener('pause', onPause);
        audio.addEventListener('ended', onEnded);
        audio.addEventListener('error', onError);
        audio.addEventListener('waiting', onWaiting);
        audio.addEventListener('playing', onPlaying);
        audio.addEventListener('timeupdate', onTimeUpdate);

        return () => {
            audio.removeEventListener('loadedmetadata', onLoadedMeta);
            audio.removeEventListener('canplay', onCanPlay);
            audio.removeEventListener('play', onPlay);
            audio.removeEventListener('pause', onPause);
            audio.removeEventListener('ended', onEnded);
            audio.removeEventListener('error', onError);
            audio.removeEventListener('waiting', onWaiting);
            audio.removeEventListener('playing', onPlaying);
            audio.removeEventListener('timeupdate', onTimeUpdate);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Smooth currentTime updates via requestAnimationFrame when playing
    useEffect(() => {
        const tick = () => {
            const audio = audioRef.current;
            if (audio && !audio.paused) {
                setCurrentTime(audio.currentTime);
            }
            rafRef.current = window.requestAnimationFrame(tick);
        };
        if (isPlaying) {
            rafRef.current = window.requestAnimationFrame(tick);
        }
        return () => {
            if (rafRef.current != null) {
                window.cancelAnimationFrame(rafRef.current);
                rafRef.current = null;
            }
        };
    }, [isPlaying]);

    // Volume changes should flow to the element live
    useEffect(() => {
        if (audioRef.current) {
            audioRef.current.volume = volume;
            audioRef.current.muted = muted;
        }
    }, [volume, muted]);

    // What happens when the current song finishes naturally.
    const handleTrackEndedRef = useRef<() => void>(() => {});
    const handleTrackEnded = useCallback(() => {
        // Repeat one: replay the same track
        if (repeat === 'one' && current && audioRef.current) {
            audioRef.current.currentTime = 0;
            audioRef.current.play().catch(() => setStatus('paused'));
            analyticsRef.current = { songId: current.id, startedAt: 0, secondsPlayed: 0, reported: false };
            return;
        }
        // Next song in queue
        if (queue.length > 0) {
            const [nextSong, ...rest] = queue;
            playInternal(nextSong, rest, current ? [current, ...history] : history);
            return;
        }
        // Repeat all: restart from original queue (or history reversed)
        if (repeat === 'all') {
            const restart = originalQueue ?? [...history].reverse();
            if (restart.length > 0) {
                const [first, ...rest] = restart;
                playInternal(first, rest, []);
                return;
            }
        }
        // Otherwise stop
        setStatus('paused');
        setCurrentTime(0);
        if (audioRef.current) {
            audioRef.current.currentTime = 0;
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [repeat, queue, current, history, originalQueue]);
    handleTrackEndedRef.current = handleTrackEnded;

    // Core playback trigger — used by both external `play()` and internal transitions.
    const playInternal = useCallback((song: SongPayload, nextQueue: SongPayload[], nextHistory: SongPayload[]) => {
        // Emit analytics for the OUTGOING song if it had accumulated time
        if (analyticsRef.current.songId != null && analyticsRef.current.songId !== song.id) {
            reportStream(false);
        }

        setCurrent(song);
        setQueue(nextQueue);
        setHistory(nextHistory);
        setCurrentTime(0);
        setDuration(song.duration ?? 0);
        setError(null);

        const audio = audioRef.current;
        if (!audio) return;

        if (!song.audio) {
            // No audio uploaded yet — show it as "current" but idle
            audio.removeAttribute('src');
            audio.load();
            setStatus('paused');
            setError('This song does not have audio uploaded yet.');
            return;
        }

        analyticsRef.current = { songId: song.id, startedAt: 0, secondsPlayed: 0, reported: false };
        audio.src = song.audio;
        setStatus('loading');
        audio.play().catch(() => {
            // Autoplay may be blocked before user gesture — stay paused
            setStatus('paused');
        });
    }, [reportStream]);

    const play = useCallback((song: SongPayload, incomingQueue?: SongPayload[]) => {
        const provided = incomingQueue ?? [];
        // When starting a fresh queue (e.g., clicking a song inside an album), keep
        // the original untouched so shuffle toggling can restore the natural order.
        const nextOriginal = provided.length > 0 ? [song, ...provided] : originalQueue ?? [song];
        setOriginalQueue(nextOriginal);
        const upcoming = shuffle && provided.length > 0 ? shuffleArray(provided) : provided;
        playInternal(song, upcoming, current ? [current, ...history].slice(0, 50) : history);
    }, [shuffle, current, history, originalQueue, playInternal]);

    const togglePlay = useCallback(() => {
        const audio = audioRef.current;
        if (!audio || !current) return;
        if (audio.paused) {
            audio.play().catch(() => setStatus('paused'));
        } else {
            audio.pause();
        }
    }, [current]);

    const pause = useCallback(() => {
        audioRef.current?.pause();
    }, []);

    const resume = useCallback(() => {
        audioRef.current?.play().catch(() => setStatus('paused'));
    }, []);

    const next = useCallback(() => {
        if (queue.length === 0) {
            if (repeat === 'all' && originalQueue && originalQueue.length > 0) {
                const [first, ...rest] = originalQueue;
                playInternal(first, rest, []);
            } else if (audioRef.current) {
                audioRef.current.pause();
                audioRef.current.currentTime = 0;
                setStatus('paused');
                setCurrentTime(0);
            }
            return;
        }
        const [nextSong, ...rest] = queue;
        playInternal(nextSong, rest, current ? [current, ...history].slice(0, 50) : history);
    }, [queue, repeat, originalQueue, current, history, playInternal]);

    const previous = useCallback(() => {
        // If we're > 3 seconds in, restart the current track
        const audio = audioRef.current;
        if (audio && audio.currentTime > 3) {
            audio.currentTime = 0;
            setCurrentTime(0);
            return;
        }
        // Otherwise go back to previous in history
        if (history.length > 0) {
            const [prevSong, ...restHistory] = history;
            const nextQueue = current ? [current, ...queue] : queue;
            playInternal(prevSong, nextQueue, restHistory);
        } else if (audio) {
            audio.currentTime = 0;
            setCurrentTime(0);
        }
    }, [history, current, queue, playInternal]);

    const seek = useCallback((seconds: number) => {
        const audio = audioRef.current;
        if (!audio) return;
        const clamped = Math.max(0, Math.min(seconds, audio.duration || seconds));
        audio.currentTime = clamped;
        setCurrentTime(clamped);
    }, []);

    const setVolume = useCallback((v: number) => {
        const clamped = Math.max(0, Math.min(1, v));
        setVolumeState(clamped);
        if (clamped === 0) setMuted(true);
        else if (muted) setMuted(false);
    }, [muted]);

    const toggleMute = useCallback(() => {
        setMuted((m) => !m);
    }, []);

    const toggleShuffle = useCallback(() => {
        setShuffle((prev) => {
            const nextValue = !prev;
            // When enabling shuffle mid-playback, shuffle the remaining queue
            // (not the currently playing song). When disabling, restore natural order
            // from originalQueue relative to current.
            if (nextValue) {
                setQueue((q) => shuffleArray(q));
            } else if (originalQueue && current) {
                const idx = originalQueue.findIndex((s) => s.id === current.id);
                if (idx >= 0) {
                    setQueue(originalQueue.slice(idx + 1));
                }
            }
            return nextValue;
        });
    }, [originalQueue, current]);

    const cycleRepeat = useCallback(() => {
        setRepeat((r) => (r === 'off' ? 'all' : r === 'all' ? 'one' : 'off'));
    }, []);

    const enqueue = useCallback((songs: SongPayload[]) => {
        setQueue((q) => [...q, ...songs]);
        setOriginalQueue((orig) => (orig ? [...orig, ...songs] : [...(current ? [current] : []), ...songs]));
    }, [current]);

    const clearQueue = useCallback(() => {
        setQueue([]);
    }, []);

    const playAt = useCallback((index: number) => {
        if (index < 0 || index >= queue.length) return;
        const target = queue[index];
        const remaining = queue.slice(index + 1);
        const skipped = queue.slice(0, index);
        const newHistory = current
            ? [current, ...skipped.reverse(), ...history].slice(0, 50)
            : [...skipped.reverse(), ...history].slice(0, 50);
        playInternal(target, remaining, newHistory);
    }, [queue, current, history, playInternal]);

    const retry = useCallback(() => {
        if (!current) return;
        setError(null);
        const audio = audioRef.current;
        if (audio && current.audio) {
            audio.src = current.audio;
            audio.play().catch(() => setStatus('paused'));
        }
    }, [current]);

    // Fire a final analytics ping when the tab is closed
    useEffect(() => {
        const beforeUnload = () => {
            if (analyticsRef.current.startedAt > 0) {
                const delta = (performance.now() - analyticsRef.current.startedAt) / 1000;
                analyticsRef.current.secondsPlayed += delta;
            }
            reportStream(false);
        };
        window.addEventListener('beforeunload', beforeUnload);
        return () => window.removeEventListener('beforeunload', beforeUnload);
    }, [reportStream]);

    const value = useMemo<PlayerContextValue>(() => ({
        current, queue, history,
        isPlaying, status,
        currentTime, duration,
        volume, muted, shuffle, repeat,
        error, fullscreen, queueOpen,
        play, togglePlay, pause, resume,
        next, previous, seek,
        setVolume, toggleMute, toggleShuffle, cycleRepeat,
        enqueue, clearQueue, playAt,
        setFullscreen, setQueueOpen, retry,
    }), [
        current, queue, history,
        isPlaying, status,
        currentTime, duration,
        volume, muted, shuffle, repeat,
        error, fullscreen, queueOpen,
        play, togglePlay, pause, resume,
        next, previous, seek,
        setVolume, toggleMute, toggleShuffle, cycleRepeat,
        enqueue, clearQueue, playAt,
        retry,
    ]);

    return <PlayerContext.Provider value={value}>{children}</PlayerContext.Provider>;
}

export function usePlayer(): PlayerContextValue {
    const ctx = useContext(PlayerContext);
    if (!ctx) throw new Error('usePlayer must be used within PlayerProvider');
    return ctx;
}

export function formatDuration(seconds?: number | null): string {
    if (seconds == null || Number.isNaN(seconds) || !Number.isFinite(seconds)) return '0:00';
    const total = Math.max(0, Math.floor(seconds));
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}
