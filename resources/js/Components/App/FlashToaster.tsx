import { usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Info, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { PageProps } from '@/types';

type FlashProps = PageProps<{
    flash?: {
        success?: string | null;
        error?: string | null;
        info?: string | null;
    };
}>;

interface Toast {
    id: number;
    kind: 'success' | 'error' | 'info';
    message: string;
}

let toastId = 0;

export default function FlashToaster() {
    const { props } = usePage<FlashProps>();
    const [toasts, setToasts] = useState<Toast[]>([]);

    useEffect(() => {
        const items: Toast[] = [];
        if (props.flash?.success) items.push({ id: ++toastId, kind: 'success', message: props.flash.success });
        if (props.flash?.error) items.push({ id: ++toastId, kind: 'error', message: props.flash.error });
        if (props.flash?.info) items.push({ id: ++toastId, kind: 'info', message: props.flash.info });

        if (items.length > 0) {
            setToasts((prev) => [...prev, ...items]);
            const timers = items.map((t) => setTimeout(() => dismiss(t.id), 4500));
            return () => timers.forEach(clearTimeout);
        }
    }, [props.flash?.success, props.flash?.error, props.flash?.info]);

    const dismiss = (id: number) => setToasts((prev) => prev.filter((t) => t.id !== id));

    if (toasts.length === 0) return null;

    return (
        <div className="fixed bottom-24 sm:bottom-28 right-4 z-50 flex flex-col gap-2 max-w-sm w-[calc(100vw-2rem)] sm:w-auto">
            {toasts.map((t) => (
                <Toast key={t.id} toast={t} onDismiss={() => dismiss(t.id)} />
            ))}
        </div>
    );
}

function Toast({ toast, onDismiss }: { toast: Toast; onDismiss: () => void }) {
    const style = {
        success: { bg: 'bg-brand-600', icon: CheckCircle2 },
        error: { bg: 'bg-rose-600', icon: AlertCircle },
        info: { bg: 'bg-sky-600', icon: Info },
    }[toast.kind];

    const Icon = style.icon;

    return (
        <div
            role="status"
            className={`${style.bg} text-white rounded-2xl shadow-lg px-4 py-3 flex items-start gap-3 animate-[slideIn_.2s_ease-out]`}
        >
            <Icon className="h-5 w-5 shrink-0 mt-0.5" />
            <p className="flex-1 text-sm">{toast.message}</p>
            <button onClick={onDismiss} aria-label="Dismiss" className="text-white/80 hover:text-white shrink-0">
                <X className="h-4 w-4" />
            </button>
            <style>{`@keyframes slideIn { from { transform: translateX(100%); opacity: 0 } to { transform: translateX(0); opacity: 1 } }`}</style>
        </div>
    );
}
