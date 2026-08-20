import { useCallback, useRef, useState, useEffect } from 'react';
import { usePlayer } from '@/Contexts/PlayerContext';

interface Props {
    /** Height of the track in px (default 4) */
    height?: number;
    /** Show tabular time labels flanking the bar */
    showLabels?: boolean;
    /** Size of the labels */
    labelSize?: 'xs' | 'sm';
    className?: string;
}

/**
 * Interactive playback progress bar.
 *
 * Uses pointer events (not <input type="range">) so we can style it precisely and
 * animate the fill smoothly. Clicking anywhere seeks; dragging scrubs live and
 * commits on release.
 *
 * While the user is dragging, we display the local drag position — not the
 * audio element's currentTime — so it feels immediately responsive.
 */
export default function Scrubber({ height = 4, showLabels = true, labelSize = 'xs', className = '' }: Props) {
    const player = usePlayer();
    const trackRef = useRef<HTMLDivElement>(null);
    const [dragging, setDragging] = useState(false);
    const [dragTime, setDragTime] = useState(0);

    const totalDuration = player.duration || player.current?.duration || 0;
    const displayTime = dragging ? dragTime : player.currentTime;
    const percent = totalDuration > 0 ? Math.min(100, (displayTime / totalDuration) * 100) : 0;

    const computeTimeFromEvent = useCallback((clientX: number) => {
        const el = trackRef.current;
        if (!el || totalDuration <= 0) return 0;
        const rect = el.getBoundingClientRect();
        const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
        return ratio * totalDuration;
    }, [totalDuration]);

    const onPointerDown = useCallback((e: React.PointerEvent) => {
        if (!player.current || totalDuration <= 0) return;
        (e.target as Element).setPointerCapture?.(e.pointerId);
        setDragging(true);
        setDragTime(computeTimeFromEvent(e.clientX));
    }, [player.current, totalDuration, computeTimeFromEvent]);

    const onPointerMove = useCallback((e: React.PointerEvent) => {
        if (!dragging) return;
        setDragTime(computeTimeFromEvent(e.clientX));
    }, [dragging, computeTimeFromEvent]);

    const onPointerUp = useCallback((e: React.PointerEvent) => {
        if (!dragging) return;
        const t = computeTimeFromEvent(e.clientX);
        player.seek(t);
        setDragging(false);
        (e.target as Element).releasePointerCapture?.(e.pointerId);
    }, [dragging, computeTimeFromEvent, player]);

    // Keyboard: focused bar accepts arrow keys for 5-second seek
    const onKeyDown = useCallback((e: React.KeyboardEvent) => {
        if (!player.current) return;
        if (e.key === 'ArrowLeft') { e.preventDefault(); player.seek(Math.max(0, player.currentTime - 5)); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); player.seek(Math.min(totalDuration, player.currentTime + 5)); }
    }, [player, totalDuration]);

    useEffect(() => {
        // Global pointerup safeguard — if the pointer leaves the element mid-drag
        const onGlobalUp = () => { if (dragging) setDragging(false); };
        window.addEventListener('pointerup', onGlobalUp);
        return () => window.removeEventListener('pointerup', onGlobalUp);
    }, [dragging]);

    const labelClass = labelSize === 'sm' ? 'text-xs' : 'text-[11px]';

    return (
        <div className={`flex items-center gap-2 ${className}`}>
            {showLabels && (
                <span className={`${labelClass} tabular-nums text-slate-500 w-10 text-right shrink-0`}>
                    {format(displayTime)}
                </span>
            )}
            <div
                ref={trackRef}
                role="slider"
                aria-label="Playback progress"
                aria-valuemin={0}
                aria-valuemax={totalDuration}
                aria-valuenow={displayTime}
                tabIndex={player.current ? 0 : -1}
                onPointerDown={onPointerDown}
                onPointerMove={onPointerMove}
                onPointerUp={onPointerUp}
                onKeyDown={onKeyDown}
                className="group relative flex-1 cursor-pointer flex items-center touch-none select-none"
                style={{ height: Math.max(height + 12, 16) }}
            >
                {/* Track */}
                <div
                    className="w-full rounded-full bg-slate-200 overflow-hidden"
                    style={{ height }}
                >
                    <div
                        className={`h-full rounded-full bg-brand-500 group-hover:bg-brand-600 ${dragging ? '' : 'transition-[width] duration-100 ease-linear'}`}
                        style={{ width: `${percent}%` }}
                    />
                </div>
                {/* Draggable thumb — visible on hover, always visible while dragging */}
                <div
                    className={`absolute h-3 w-3 rounded-full bg-brand-600 border-2 border-white shadow -translate-x-1/2 ${dragging ? 'opacity-100 scale-110' : 'opacity-0 group-hover:opacity-100'} transition-opacity`}
                    style={{ left: `${percent}%` }}
                    aria-hidden="true"
                />
            </div>
            {showLabels && (
                <span className={`${labelClass} tabular-nums text-slate-500 w-10 shrink-0`}>
                    {format(totalDuration)}
                </span>
            )}
        </div>
    );
}

function format(seconds: number): string {
    if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
    const total = Math.floor(seconds);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}
