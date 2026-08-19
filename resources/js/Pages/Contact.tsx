import { useForm } from '@inertiajs/react';
import { Loader2, Mail, MapPin, MessageCircle } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

export default function Contact() {
    const form = useForm({
        name: '',
        email: '',
        subject: 'general',
        message: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/contact');
    };

    return (
        <AppLayout title="Contact">
            <div className="max-w-3xl mx-auto space-y-8">
                <header>
                    <p className="text-xs font-semibold uppercase tracking-widest text-brand-700 mb-2">
                        Get in touch
                    </p>
                    <h1 className="text-3xl md:text-4xl font-bold text-ink">Contact us</h1>
                    <p className="text-sm md:text-base text-slate-600 mt-2 max-w-xl">
                        Questions about submissions, copyright, a bug on the site, or you'd like to
                        partner with us — send a message and we'll get back to you.
                    </p>
                </header>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="rounded-2xl bg-white border border-slate-200 p-4">
                        <span className="h-9 w-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                            <Mail className="h-4 w-4" />
                        </span>
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Email</p>
                        <a href="mailto:hello@malawiadventistmusic.com" className="text-sm text-ink hover:text-brand-700">
                            hello@malawiadventistmusic.com
                        </a>
                    </div>
                    <div className="rounded-2xl bg-white border border-slate-200 p-4">
                        <span className="h-9 w-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                            <MapPin className="h-4 w-4" />
                        </span>
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Based in</p>
                        <p className="text-sm text-ink">Malawi</p>
                    </div>
                    <div className="rounded-2xl bg-white border border-slate-200 p-4">
                        <span className="h-9 w-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-3">
                            <MessageCircle className="h-4 w-4" />
                        </span>
                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Response time</p>
                        <p className="text-sm text-ink">Within 2 business days</p>
                    </div>
                </div>

                <form onSubmit={submit} className="rounded-2xl bg-white border border-slate-200 shadow-card p-6 space-y-4">
                    <h2 className="text-base font-semibold text-ink">Send us a message</h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label className="block">
                            <span className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Your name</span>
                            <input
                                type="text"
                                required
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                            />
                            {form.errors.name && <p className="text-xs text-rose-600 mt-1">{form.errors.name}</p>}
                        </label>
                        <label className="block">
                            <span className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Email</span>
                            <input
                                type="email"
                                required
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                            />
                            {form.errors.email && <p className="text-xs text-rose-600 mt-1">{form.errors.email}</p>}
                        </label>
                    </div>

                    <label className="block">
                        <span className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Subject</span>
                        <select
                            value={form.data.subject}
                            onChange={(e) => form.setData('subject', e.target.value)}
                            className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        >
                            <option value="general">General question</option>
                            <option value="submission">Submission help</option>
                            <option value="payment">Payment or refund</option>
                            <option value="copyright">Copyright concern</option>
                            <option value="partnership">Partnership / advertising</option>
                            <option value="bug">Report a bug</option>
                        </select>
                    </label>

                    <label className="block">
                        <span className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Message</span>
                        <textarea
                            rows={6}
                            required
                            value={form.data.message}
                            onChange={(e) => form.setData('message', e.target.value)}
                            className="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                        {form.errors.message && <p className="text-xs text-rose-600 mt-1">{form.errors.message}</p>}
                    </label>

                    <div className="flex items-center justify-end">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 disabled:bg-slate-300 text-white text-sm font-semibold px-5 py-2.5"
                        >
                            {form.processing && <Loader2 className="h-4 w-4 animate-spin" />}
                            Send message
                        </button>
                    </div>
                    {form.recentlySuccessful && (
                        <p className="text-sm text-brand-700">Thanks — we'll get back to you within 2 business days.</p>
                    )}
                </form>
            </div>
        </AppLayout>
    );
}
