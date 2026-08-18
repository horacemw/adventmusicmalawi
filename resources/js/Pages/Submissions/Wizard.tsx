import { router, useForm } from '@inertiajs/react';
import clsx from 'clsx';
import {
    ArrowRight,
    Check,
    CheckCircle2,
    FileAudio,
    ImageIcon,
    Loader2,
    Music2,
    ShieldCheck,
    Trash2,
    Upload,
    User as UserIcon,
} from 'lucide-react';
import { useMemo, useState, type ChangeEvent } from 'react';
import AppLayout from '@/Layouts/AppLayout';

interface Option { id: number; name: string; }
interface DistrictOption extends Option { region_id: number | null; }

interface WizardProps {
    submission: {
        id: number;
        reference: string;
        status: string;
        submitter_name: string;
        submitter_email: string;
        submitter_phone: string | null;
        song_title: string;
        artist_name: string | null;
        group_name: string | null;
        choir_name: string | null;
        church_name: string | null;
        album_title: string | null;
        release_year: number | null;
        description: string | null;
        language_id: number | null;
        genre_id: number | null;
        region_id: number | null;
        district_id: number | null;
        copyright_owner: string | null;
        rights_holder: string | null;
        permission_status: string | null;
        owner_confirmation: boolean;
        platform_distribution_permission: boolean;
        accuracy_confirmation: boolean;
        copyright_notes: string | null;
        category_ids: number[];
        occasion_ids: number[];
        mood_ids: number[];
        files: Array<{
            id: number;
            kind: string;
            original_name: string;
            size_bytes: number | null;
            url: string | null;
        }>;
    };
    options: {
        languages: Option[];
        genres: Option[];
        categories: Option[];
        occasions: Option[];
        moods: Option[];
        regions: Option[];
        districts: DistrictOption[];
    };
    fee: { amount: number; currency: string; };
}

const STEPS = [
    { id: 1, label: 'Your details' },
    { id: 2, label: 'Music details' },
    { id: 3, label: 'Media' },
    { id: 4, label: 'Copyright' },
    { id: 5, label: 'Review' },
    { id: 6, label: 'Payment' },
];

function StepIndicator({ current }: { current: number }) {
    return (
        <ol className="flex items-center gap-1 sm:gap-2 mb-8 overflow-x-auto scrollbar-none">
            {STEPS.map((s) => {
                const done = s.id < current;
                const active = s.id === current;
                return (
                    <li key={s.id} className="flex items-center gap-2 shrink-0">
                        <span
                            className={clsx(
                                'h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0',
                                done && 'bg-brand-600 text-white',
                                active && 'bg-brand-100 text-brand-700 ring-2 ring-brand-600',
                                !done && !active && 'bg-slate-100 text-slate-500',
                            )}
                        >
                            {done ? <Check className="h-4 w-4" /> : s.id}
                        </span>
                        <span
                            className={clsx(
                                'text-xs sm:text-sm font-medium whitespace-nowrap',
                                active ? 'text-ink' : 'text-slate-400',
                            )}
                        >
                            {s.label}
                        </span>
                        {s.id !== STEPS.length && (
                            <span className="hidden sm:block w-6 h-px bg-slate-200 mx-1" />
                        )}
                    </li>
                );
            })}
        </ol>
    );
}

function formatBytes(n: number | null): string {
    if (!n) return '';
    if (n < 1024) return n + ' B';
    if (n < 1024 * 1024) return Math.round(n / 1024) + ' KB';
    return (n / (1024 * 1024)).toFixed(1) + ' MB';
}

function money(n: number, ccy: string): string {
    return new Intl.NumberFormat('en-MW').format(n) + ' ' + ccy;
}

export default function Wizard({ submission, options, fee }: WizardProps) {
    const [step, setStep] = useState<number>(submission.song_title ? 2 : 1);

    const form = useForm({
        submitter_name: submission.submitter_name || '',
        submitter_email: submission.submitter_email || '',
        submitter_phone: submission.submitter_phone || '',
        song_title: submission.song_title || '',
        artist_name: submission.artist_name || '',
        group_name: submission.group_name || '',
        choir_name: submission.choir_name || '',
        church_name: submission.church_name || '',
        album_title: submission.album_title || '',
        release_year: submission.release_year ?? ('' as number | string),
        description: submission.description || '',
        language_id: submission.language_id ?? ('' as number | string),
        genre_id: submission.genre_id ?? ('' as number | string),
        region_id: submission.region_id ?? ('' as number | string),
        district_id: submission.district_id ?? ('' as number | string),
        copyright_owner: submission.copyright_owner || '',
        rights_holder: submission.rights_holder || '',
        permission_status: submission.permission_status || 'owned',
        owner_confirmation: submission.owner_confirmation,
        platform_distribution_permission: submission.platform_distribution_permission,
        accuracy_confirmation: submission.accuracy_confirmation,
        copyright_notes: submission.copyright_notes || '',
        category_ids: submission.category_ids,
        occasion_ids: submission.occasion_ids,
        mood_ids: submission.mood_ids,
    });

    const districtsForRegion = useMemo(() => {
        if (!form.data.region_id) return options.districts;
        return options.districts.filter((d) => d.region_id === Number(form.data.region_id));
    }, [form.data.region_id, options.districts]);

    const save = (next?: number) => {
        form.put(`/submissions/${submission.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                if (next) setStep(next);
            },
        });
    };

    const toggleMulti = (field: 'category_ids' | 'occasion_ids' | 'mood_ids', id: number) => {
        const current = (form.data[field] as number[]) ?? [];
        form.setData(field, current.includes(id) ? current.filter((x) => x !== id) : [...current, id]);
    };

    const filesByKind = (kind: string) => submission.files.filter((f) => f.kind === kind);
    const audioFile = filesByKind('audio')[0];
    const artworkFile = filesByKind('artwork')[0];
    const permissionFile = filesByKind('permission_document')[0];

    const uploadFile = (kind: string) => (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const data = new FormData();
        data.append('file', file);
        data.append('kind', kind);
        router.post(`/submissions/${submission.id}/files`, data, {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    const deleteFile = (id: number) => {
        if (!confirm('Remove this file?')) return;
        router.delete(`/submissions/${submission.id}/files/${id}`, { preserveScroll: true });
    };

    const canProceedToPayment =
        !!form.data.song_title &&
        !!audioFile &&
        form.data.owner_confirmation &&
        form.data.platform_distribution_permission &&
        form.data.accuracy_confirmation;

    const submitPayment = () => {
        if (!canProceedToPayment) return;
        router.post(`/submissions/${submission.id}/pay`, {}, { preserveScroll: false });
    };

    return (
        <AppLayout title="Submit music">
            <div className="max-w-3xl mx-auto space-y-6">
                <header>
                    <p className="text-xs font-semibold uppercase tracking-widest text-brand-700 mb-1">
                        Submit your music
                    </p>
                    <h1 className="text-2xl md:text-3xl font-bold text-ink">
                        Share your song with Malawi
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        Reference{' '}
                        <span className="font-mono text-slate-700">
                            {submission.reference}
                        </span>
                    </p>
                </header>

                <StepIndicator current={step} />

                <div className="rounded-2xl bg-white border border-slate-200 shadow-card p-5 sm:p-8 space-y-6">
                    {step === 1 && (
                        <Section
                            icon={<UserIcon className="h-5 w-5" />}
                            title="Your details"
                            hint="Where should we reach you about this submission?"
                        >
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="Full name" error={form.errors.submitter_name}>
                                    <input
                                        type="text"
                                        value={form.data.submitter_name}
                                        onChange={(e) => form.setData('submitter_name', e.target.value)}
                                        className="input"
                                    />
                                </Field>
                                <Field label="Email" error={form.errors.submitter_email}>
                                    <input
                                        type="email"
                                        value={form.data.submitter_email}
                                        onChange={(e) => form.setData('submitter_email', e.target.value)}
                                        className="input"
                                    />
                                </Field>
                                <Field label="Phone" error={form.errors.submitter_phone}>
                                    <input
                                        type="tel"
                                        value={form.data.submitter_phone}
                                        onChange={(e) => form.setData('submitter_phone', e.target.value)}
                                        className="input"
                                        placeholder="+265..."
                                    />
                                </Field>
                            </div>
                        </Section>
                    )}

                    {step === 2 && (
                        <Section
                            icon={<Music2 className="h-5 w-5" />}
                            title="Music details"
                            hint="Tell us about the song."
                        >
                            <Field label="Song title *" error={form.errors.song_title}>
                                <input
                                    type="text"
                                    value={form.data.song_title}
                                    onChange={(e) => form.setData('song_title', e.target.value)}
                                    className="input"
                                    required
                                />
                            </Field>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="Artist name">
                                    <input type="text" value={form.data.artist_name} onChange={(e) => form.setData('artist_name', e.target.value)} className="input" />
                                </Field>
                                <Field label="Music group / choir">
                                    <input type="text" value={form.data.group_name} onChange={(e) => form.setData('group_name', e.target.value)} className="input" />
                                </Field>
                                <Field label="Church">
                                    <input type="text" value={form.data.church_name} onChange={(e) => form.setData('church_name', e.target.value)} className="input" />
                                </Field>
                                <Field label="Album (optional)">
                                    <input type="text" value={form.data.album_title} onChange={(e) => form.setData('album_title', e.target.value)} className="input" />
                                </Field>
                                <Field label="Release year">
                                    <input type="number" min={1900} max={2100} value={form.data.release_year} onChange={(e) => form.setData('release_year', e.target.value)} className="input" />
                                </Field>
                                <Field label="Language">
                                    <select value={form.data.language_id} onChange={(e) => form.setData('language_id', e.target.value)} className="input">
                                        <option value="">—</option>
                                        {options.languages.map((l) => <option key={l.id} value={l.id}>{l.name}</option>)}
                                    </select>
                                </Field>
                                <Field label="Genre">
                                    <select value={form.data.genre_id} onChange={(e) => form.setData('genre_id', e.target.value)} className="input">
                                        <option value="">—</option>
                                        {options.genres.map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                                    </select>
                                </Field>
                                <Field label="Region">
                                    <select value={form.data.region_id} onChange={(e) => { form.setData('region_id', e.target.value); form.setData('district_id', ''); }} className="input">
                                        <option value="">—</option>
                                        {options.regions.map((r) => <option key={r.id} value={r.id}>{r.name}</option>)}
                                    </select>
                                </Field>
                                <Field label="District">
                                    <select value={form.data.district_id} onChange={(e) => form.setData('district_id', e.target.value)} className="input" disabled={!form.data.region_id}>
                                        <option value="">—</option>
                                        {districtsForRegion.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                                    </select>
                                </Field>
                            </div>

                            <Field label="Description">
                                <textarea rows={4} value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className="input" placeholder="What is the song about?" />
                            </Field>

                            <Field label="Categories">
                                <ChipPicker options={options.categories} selected={form.data.category_ids} onToggle={(id) => toggleMulti('category_ids', id)} />
                            </Field>
                            <Field label="Occasions">
                                <ChipPicker options={options.occasions} selected={form.data.occasion_ids} onToggle={(id) => toggleMulti('occasion_ids', id)} />
                            </Field>
                            <Field label="Moods">
                                <ChipPicker options={options.moods} selected={form.data.mood_ids} onToggle={(id) => toggleMulti('mood_ids', id)} />
                            </Field>
                        </Section>
                    )}

                    {step === 3 && (
                        <Section
                            icon={<Upload className="h-5 w-5" />}
                            title="Upload media"
                            hint="Audio is required. Artwork is highly recommended."
                        >
                            <UploadRow
                                label="Audio (MP3, AAC, WAV) — required"
                                icon={<FileAudio className="h-5 w-5" />}
                                file={audioFile}
                                onUpload={uploadFile('audio')}
                                onDelete={audioFile ? () => deleteFile(audioFile.id) : undefined}
                                accept="audio/mpeg,audio/mp4,audio/aac,audio/wav,audio/x-wav"
                            />
                            <UploadRow
                                label="Album artwork (JPEG/PNG)"
                                icon={<ImageIcon className="h-5 w-5" />}
                                file={artworkFile}
                                onUpload={uploadFile('artwork')}
                                onDelete={artworkFile ? () => deleteFile(artworkFile.id) : undefined}
                                accept="image/jpeg,image/png,image/webp"
                            />
                            <UploadRow
                                label="Permission document (optional PDF/image)"
                                icon={<ShieldCheck className="h-5 w-5" />}
                                file={permissionFile}
                                onUpload={uploadFile('permission_document')}
                                onDelete={permissionFile ? () => deleteFile(permissionFile.id) : undefined}
                                accept="application/pdf,image/png,image/jpeg"
                            />
                        </Section>
                    )}

                    {step === 4 && (
                        <Section
                            icon={<ShieldCheck className="h-5 w-5" />}
                            title="Copyright & permission"
                            hint="Please confirm you have the right to distribute this recording."
                        >
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="Copyright owner">
                                    <input type="text" value={form.data.copyright_owner} onChange={(e) => form.setData('copyright_owner', e.target.value)} className="input" />
                                </Field>
                                <Field label="Rights holder">
                                    <input type="text" value={form.data.rights_holder} onChange={(e) => form.setData('rights_holder', e.target.value)} className="input" />
                                </Field>
                                <Field label="Permission status">
                                    <select value={form.data.permission_status} onChange={(e) => form.setData('permission_status', e.target.value)} className="input">
                                        <option value="owned">I own it</option>
                                        <option value="licensed">Licensed</option>
                                        <option value="permission_granted">Written permission granted</option>
                                        <option value="public_domain">Public domain</option>
                                        <option value="unknown">Not sure</option>
                                    </select>
                                </Field>
                            </div>

                            <Field label="Notes to the moderator (optional)">
                                <textarea rows={3} value={form.data.copyright_notes} onChange={(e) => form.setData('copyright_notes', e.target.value)} className="input" />
                            </Field>

                            <div className="space-y-2 pt-2">
                                <Checkbox
                                    checked={form.data.owner_confirmation}
                                    onChange={(v) => form.setData('owner_confirmation', v)}
                                    label="I own this recording, or I have permission from the rights holder to distribute it."
                                />
                                <Checkbox
                                    checked={form.data.platform_distribution_permission}
                                    onChange={(v) => form.setData('platform_distribution_permission', v)}
                                    label="I grant Malawi Adventist Music permission to stream this recording."
                                />
                                <Checkbox
                                    checked={form.data.accuracy_confirmation}
                                    onChange={(v) => form.setData('accuracy_confirmation', v)}
                                    label="Everything I have entered is accurate to the best of my knowledge."
                                />
                            </div>
                        </Section>
                    )}

                    {step === 5 && (
                        <Section
                            icon={<CheckCircle2 className="h-5 w-5" />}
                            title="Review"
                            hint="Please check everything before paying."
                        >
                            <ReviewRow label="Submitter" value={`${form.data.submitter_name} · ${form.data.submitter_email}`} />
                            <ReviewRow label="Song" value={form.data.song_title} />
                            <ReviewRow label="Artist / Group" value={[form.data.artist_name, form.data.group_name, form.data.church_name].filter(Boolean).join(' · ') || '—'} />
                            <ReviewRow label="Language / Genre" value={[options.languages.find((l) => l.id === Number(form.data.language_id))?.name, options.genres.find((g) => g.id === Number(form.data.genre_id))?.name].filter(Boolean).join(' · ') || '—'} />
                            <ReviewRow label="Audio" value={audioFile ? audioFile.original_name : <span className="text-rose-600">Missing</span>} />
                            <ReviewRow label="Artwork" value={artworkFile ? artworkFile.original_name : '—'} />
                            <ReviewRow
                                label="Confirmations"
                                value={
                                    form.data.owner_confirmation &&
                                    form.data.platform_distribution_permission &&
                                    form.data.accuracy_confirmation
                                        ? 'All three confirmed'
                                        : <span className="text-rose-600">Not fully confirmed</span>
                                }
                            />

                            <div className="rounded-xl bg-brand-50 text-brand-900 p-4 flex items-center gap-3 mt-4">
                                <span className="h-9 w-9 rounded-full bg-brand-600 text-white flex items-center justify-center shrink-0">
                                    <ShieldCheck className="h-4 w-4" />
                                </span>
                                <div>
                                    <p className="text-sm font-semibold">
                                        Submission fee: {money(fee.amount, fee.currency)}
                                    </p>
                                    <p className="text-xs text-brand-800/80">
                                        You'll pay through PayChangu. A moderator will review your submission after payment.
                                    </p>
                                </div>
                            </div>
                        </Section>
                    )}

                    {step === 6 && (
                        <Section
                            icon={<ShieldCheck className="h-5 w-5" />}
                            title="Payment"
                            hint="You'll be redirected to PayChangu to complete your payment."
                        >
                            <div className="rounded-xl border border-slate-200 p-5">
                                <p className="text-sm text-slate-600 mb-1">Amount due</p>
                                <p className="text-2xl font-bold text-ink">{money(fee.amount, fee.currency)}</p>
                            </div>
                            {!canProceedToPayment && (
                                <div className="rounded-xl bg-rose-50 text-rose-900 p-4 text-sm">
                                    Please go back and complete all required fields, upload an audio file, and confirm the three copyright statements before paying.
                                </div>
                            )}
                            <button
                                type="button"
                                onClick={submitPayment}
                                disabled={!canProceedToPayment || form.processing}
                                className="w-full inline-flex items-center justify-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white text-sm font-semibold px-5 py-3"
                            >
                                {form.processing ? <Loader2 className="h-4 w-4 animate-spin" /> : <ShieldCheck className="h-4 w-4" />}
                                Continue to PayChangu
                            </button>
                            <p className="text-xs text-slate-500 text-center">
                                Payment is verified server-side. Your submission enters moderation only after verified payment.
                            </p>
                        </Section>
                    )}

                    {/* Nav */}
                    <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            onClick={() => setStep((s) => Math.max(1, s - 1))}
                            disabled={step === 1}
                            className="text-sm font-medium text-slate-500 hover:text-ink disabled:text-slate-300"
                        >
                            Back
                        </button>
                        {step < 5 ? (
                            <button
                                type="button"
                                onClick={() => save(step + 1)}
                                disabled={form.processing}
                                className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2"
                            >
                                {form.processing ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
                                Save & continue
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        ) : step === 5 ? (
                            <button
                                type="button"
                                onClick={() => setStep(6)}
                                className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2"
                            >
                                Go to payment
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        ) : null}
                    </div>
                </div>
            </div>

            {/* Tailwind utility class for consistent inputs */}
            <style>{`
                .input {
                    display: block;
                    width: 100%;
                    padding: 0.5rem 0.75rem;
                    border-radius: 0.5rem;
                    border: 1px solid rgb(226 232 240);
                    background: white;
                    font-size: 0.875rem;
                    color: rgb(15 23 42);
                }
                .input:focus {
                    outline: none;
                    border-color: rgb(34 197 94);
                    box-shadow: 0 0 0 3px rgb(34 197 94 / 0.15);
                }
                .input[disabled] { background: rgb(248 250 252); color: rgb(148 163 184); }
            `}</style>
        </AppLayout>
    );
}

function Section({ icon, title, hint, children }: { icon: React.ReactNode; title: string; hint?: string; children: React.ReactNode }) {
    return (
        <section>
            <div className="flex items-start gap-3 mb-4">
                <span className="h-9 w-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    {icon}
                </span>
                <div>
                    <h2 className="text-base font-semibold text-ink">{title}</h2>
                    {hint && <p className="text-xs text-slate-500">{hint}</p>}
                </div>
            </div>
            <div className="space-y-4">{children}</div>
        </section>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <label className="block">
            <span className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                {label}
            </span>
            {children}
            {error && <p className="text-xs text-rose-600 mt-1">{error}</p>}
        </label>
    );
}

function ChipPicker({ options, selected, onToggle }: { options: Option[]; selected: number[]; onToggle: (id: number) => void }) {
    return (
        <div className="flex flex-wrap gap-2">
            {options.map((o) => {
                const on = selected.includes(o.id);
                return (
                    <button
                        key={o.id}
                        type="button"
                        onClick={() => onToggle(o.id)}
                        className={clsx(
                            'px-3 py-1.5 rounded-full text-xs font-medium border transition-colors',
                            on
                                ? 'bg-brand-600 text-white border-brand-600'
                                : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300',
                        )}
                    >
                        {o.name}
                    </button>
                );
            })}
        </div>
    );
}

function Checkbox({ checked, onChange, label }: { checked: boolean; onChange: (v: boolean) => void; label: string }) {
    return (
        <label className="flex items-start gap-3 cursor-pointer">
            <input
                type="checkbox"
                checked={checked}
                onChange={(e) => onChange(e.target.checked)}
                className="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            <span className="text-sm text-slate-700">{label}</span>
        </label>
    );
}

function ReviewRow({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="grid grid-cols-3 gap-3 py-2 border-b border-slate-100 text-sm">
            <span className="text-slate-500">{label}</span>
            <span className="col-span-2 text-ink font-medium">{value}</span>
        </div>
    );
}

function UploadRow({
    label,
    icon,
    file,
    onUpload,
    onDelete,
    accept,
}: {
    label: string;
    icon: React.ReactNode;
    file?: { id: number; original_name: string; size_bytes: number | null; url: string | null };
    onUpload: (e: ChangeEvent<HTMLInputElement>) => void;
    onDelete?: () => void;
    accept?: string;
}) {
    return (
        <div className="rounded-xl border border-slate-200 p-4 flex items-center gap-4">
            <span className="h-10 w-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                {icon}
            </span>
            <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-ink">{label}</p>
                {file ? (
                    <p className="text-xs text-slate-500 truncate">
                        {file.original_name} · {formatBytes(file.size_bytes)}
                    </p>
                ) : (
                    <p className="text-xs text-slate-400">No file selected</p>
                )}
            </div>
            {file && onDelete && (
                <button
                    type="button"
                    onClick={onDelete}
                    className="p-2 text-slate-400 hover:text-rose-600"
                    aria-label="Remove"
                >
                    <Trash2 className="h-4 w-4" />
                </button>
            )}
            <label className="cursor-pointer inline-flex items-center gap-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3 py-1.5">
                <Upload className="h-3.5 w-3.5" />
                {file ? 'Replace' : 'Upload'}
                <input type="file" accept={accept} onChange={onUpload} className="hidden" />
            </label>
        </div>
    );
}
