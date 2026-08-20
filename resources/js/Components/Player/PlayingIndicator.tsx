interface Props {
    isPlaying: boolean;
    size?: number;
    className?: string;
}

/**
 * Small equalizer bars indicator shown next to the currently active song.
 * When isPlaying is true, bars animate; when paused, bars are frozen at a
 * "loud" state so the user knows the song is selected but not moving.
 */
export default function PlayingIndicator({ isPlaying, size = 14, className = '' }: Props) {
    const barBase = 'inline-block bg-brand-600 rounded-sm';
    // Static heights when paused
    const staticHeights = [0.5, 0.75, 0.4];

    return (
        <span
            className={`inline-flex items-end gap-[2px] ${className}`}
            style={{ height: size, width: size }}
            aria-label={isPlaying ? 'Now playing' : 'Currently selected'}
            role="img"
        >
            {[0, 1, 2].map((i) => (
                <span
                    key={i}
                    className={barBase}
                    style={{
                        width: 2,
                        height: isPlaying ? '100%' : `${staticHeights[i] * 100}%`,
                        animation: isPlaying ? `mam-eq ${0.6 + i * 0.12}s ease-in-out ${i * 0.15}s infinite alternate` : 'none',
                    }}
                />
            ))}
        </span>
    );
}
