import AppLayout from '@/Layouts/AppLayout';
import { Construction } from 'lucide-react';

interface Props {
    title: string;
}

export default function Placeholder({ title }: Props) {
    return (
        <AppLayout title={title}>
            <div className="rounded-3xl bg-white border border-slate-200 p-10 md:p-16 text-center shadow-card">
                <div className="mx-auto h-14 w-14 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <Construction className="h-6 w-6" />
                </div>
                <h1 className="text-2xl md:text-3xl font-bold text-ink mb-2">{title}</h1>
                <p className="text-sm md:text-base text-slate-500 max-w-md mx-auto">
                    This section is scaffolded and ready to build. The database schema,
                    models, and design system are all in place — content coming in the
                    next development phase.
                </p>
            </div>
        </AppLayout>
    );
}
