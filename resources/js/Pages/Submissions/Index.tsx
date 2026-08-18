import { Link } from '@inertiajs/react';
import { FilePlus2, ChevronRight, Music2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import clsx from 'clsx';

interface SubmissionRow {
    id: number;
    reference: string;
    song_title: string;
    status: string;
    song_id: number | null;
    created_at: string;
}

const STATUS_LABEL: Record<string, string> = {
    draft: 'Draft',
    awaiting_payment: 'Awaiting payment',
    payment_pending: 'Payment processing',
    paid: 'Paid',
    under_review: 'Under review',
    approved: 'Approved',
    rejected: 'Rejected',
    changes_requested: 'Changes requested',
    published: 'Published',
    withdrawn: 'Withdrawn',
};

const STATUS_STYLE: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    awaiting_payment: 'bg-amber-100 text-amber-800',
    payment_pending: 'bg-amber-100 text-amber-800',
    paid: 'bg-brand-100 text-brand-800',
    under_review: 'bg-sky-100 text-sky-800',
    approved: 'bg-brand-100 text-brand-800',
    rejected: 'bg-rose-100 text-rose-800',
    changes_requested: 'bg-orange-100 text-orange-800',
    published: 'bg-brand-100 text-brand-800',
    withdrawn: 'bg-slate-100 text-slate-500',
};

export default function SubmissionsIndex({ submissions }: { submissions: SubmissionRow[] }) {
    return (
        <AppLayout title="My submissions">
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-bold text-ink">My submissions</h1>
                    <Link
                        href="/submit-music"
                        className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2"
                    >
                        <FilePlus2 className="h-4 w-4" />
                        New submission
                    </Link>
                </div>

                {submissions.length === 0 ? (
                    <div className="rounded-3xl bg-white border border-slate-200 p-10 md:p-16 text-center shadow-card">
                        <div className="mx-auto h-14 w-14 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                            <Music2 className="h-6 w-6" />
                        </div>
                        <h2 className="text-lg font-semibold text-ink mb-1">
                            No submissions yet
                        </h2>
                        <p className="text-sm text-slate-500 max-w-md mx-auto mb-6">
                            Submit your first song to Malawi Adventist Music. You'll pay a
                            small fee, then our moderators will review your submission before it
                            goes live.
                        </p>
                        <Link
                            href="/submit-music"
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            Start a submission
                            <ChevronRight className="h-4 w-4" />
                        </Link>
                    </div>
                ) : (
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {submissions.map((s) => (
                            <li key={s.id}>
                                <Link
                                    href={`/submissions/${s.id}/edit`}
                                    className="flex items-center gap-4 px-4 py-3.5 hover:bg-slate-50 transition-colors"
                                >
                                    <div className="h-10 w-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                        <Music2 className="h-4 w-4" />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-semibold text-ink truncate">
                                            {s.song_title || 'Untitled draft'}
                                        </p>
                                        <p className="text-xs text-slate-500 font-mono truncate">
                                            {s.reference}
                                        </p>
                                    </div>
                                    <span
                                        className={clsx(
                                            'px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap',
                                            STATUS_STYLE[s.status] ?? 'bg-slate-100 text-slate-700',
                                        )}
                                    >
                                        {STATUS_LABEL[s.status] ?? s.status}
                                    </span>
                                    <ChevronRight className="h-4 w-4 text-slate-300 shrink-0" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
