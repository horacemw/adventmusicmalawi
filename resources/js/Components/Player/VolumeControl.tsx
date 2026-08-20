import { Volume, Volume1, Volume2, VolumeX } from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';

interface Props {
    /** Width of the slider in px */
    width?: number;
    className?: string;
}

export default function VolumeControl({ width = 96, className = '' }: Props) {
    const player = usePlayer();
    const level = player.muted ? 0 : player.volume;

    const Icon = level === 0 ? VolumeX : level < 0.34 ? Volume : level < 0.67 ? Volume1 : Volume2;

    return (
        <div className={`flex items-center gap-2 ${className}`}>
            <button
                type="button"
                onClick={player.toggleMute}
                className="text-slate-500 hover:text-ink transition-colors"
                aria-label={player.muted ? 'Unmute' : 'Mute'}
            >
                <Icon className="h-4 w-4" />
            </button>
            <input
                type="range"
                min={0}
                max={1}
                step={0.01}
                value={level}
                onChange={(e) => player.setVolume(Number(e.target.value))}
                className="mam-volume"
                style={{ width }}
                aria-label="Volume"
            />
        </div>
    );
}
