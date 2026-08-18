import { Link } from '@inertiajs/react';
import { AlertTriangle, CheckCircle2, Clock, RefreshCw, XCircle } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

interface Props {
    status: string;
    message?: string;
    reference?: string;
    tx_ref?: string;
    amount?: string | number;
    currency?: string;
    failure_reason?: string | null;
}

const CONFIG: Record<string, { icon: React.ComponentType<{ className?: string }>; tone: string; title: string; }> = {
    successful: {
        icon: CheckCircle2,
        tone: 'bg-brand-50 text-brand-700 ring-brand-100',
        title: 'Payment successful',
    },
    processing: {
        icon: Clock,
        tone: 'bg-sky-50 text-sky-700 ring-sky-100',
        title: 'Payment still processing',
    },
    pending: {
        icon: Clock,
        tone: 'bg-sky-50 text-sky-700 ring-sky-100',
        title: 'Payment pending',
    },
    failed: {
        icon: XCircle,
        tone: 'bg-rose-50 text-rose-700 ring-rose-100',
        title: 'Payment failed',
    },
    cancelled: {
        icon: XCircle,
        tone: 'bg-slate-50 text-slate-700 ring-slate-200',
        title: 'Payment cancelled',
    },
    error: {
        icon: AlertTriangle,
        tone: 'bg-amber-50 text-amber-800 ring-amber-100',
        title: 'Something went wrong',
    },
};

export default function PaymentReturn(props: Props) {
    const cfg = CONFIG[props.status] ?? CONFIG.error;
    const Icon = cfg.icon;
    const isSuccess = props.status === 'successful';

    return (
        <AppLayout title={cfg.title}>
            <div className="max-w-xl mx-auto">
                <div className="rounded-3xl bg-white border border-slate-200 shadow-card p-8 md:p-10 text-center">
                    <div
                        className={`mx-auto h-16 w-16 rounded-full flex items-center justify-center mb-5 ring-8 ${cfg.tone}`}
                    >
                        <Icon className="h-7 w-7" />
                    </div>
                    <h1 className="text-2xl md:text-3xl font-bold text-ink mb-2">
                        {cfg.title}
                    </h1>
                    {isSuccess ? (
                        <p className="text-sm text-slate-600 mb-6">
                            Thank you. Your submission is now with our moderators and you'll
                            receive an email once a decision has been made.
                        </p>
                    ) : props.status === 'pending' || props.status === 'processing' ? (
                        <p className="text-sm text-slate-600 mb-6">
                            We're still confirming your payment with PayChangu. This usually takes
                            a few seconds. Refresh in a minute or check your submissions page.
                        </p>
                    ) : props.status === 'failed' ? (
                        <p className="text-sm text-slate-600 mb-6">
                            Your payment could not be completed{props.failure_reason ? ' — ' + props.failure_reason : '.'} You can retry from your submissions page.
                        </p>
                    ) : (
                        <p className="text-sm text-slate-600 mb-6">
                            {props.message ?? 'Please head to your submissions page for the latest status.'}
                        </p>
                    )}

                    {(props.reference || props.tx_ref) && (
                        <dl className="rounded-2xl bg-slate-50 border border-slate-100 p-4 mb-6 text-left text-sm space-y-2">
                            {props.reference && (
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-slate-500">Payment reference</dt>
                                    <dd className="font-mono text-ink truncate">{props.reference}</dd>
                                </div>
                            )}
                            {props.tx_ref && (
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-slate-500">Transaction ID</dt>
                                    <dd className="font-mono text-ink truncate">{props.tx_ref}</dd>
                                </div>
                            )}
                            {props.amount && props.currency && (
                                <div className="flex items-center justify-between gap-3">
                                    <dt className="text-slate-500">Amount</dt>
                                    <dd className="font-semibold text-ink">
                                        {new Intl.NumberFormat('en-MW').format(Number(props.amount))}{' '}
                                        {props.currency}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    )}

                    <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link
                            href="/submissions"
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            My submissions
                        </Link>
                        {!isSuccess && (
                            <button
                                type="button"
                                onClick={() => window.location.reload()}
                                className="inline-flex items-center gap-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-5 py-2.5"
                            >
                                <RefreshCw className="h-4 w-4" />
                                Refresh status
                            </button>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
