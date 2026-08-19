import { Link, usePage } from '@inertiajs/react';
import { Bell, ChevronRight, KeyRound, LogOut, Music2, Shield, UserCircle } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';

interface Row {
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    label: string;
    hint: string;
    external?: boolean;
    method?: string;
}

export default function Settings() {
    const { props: { auth } } = usePage<PageProps>();
    const isAuthed = !!auth?.user;

    const authedRows: Row[] = [
        { href: '/profile', icon: UserCircle, label: 'Profile', hint: 'Name, email, phone, avatar' },
        { href: '/profile', icon: KeyRound, label: 'Password', hint: 'Change your password' },
        { href: '/notifications', icon: Bell, label: 'Notifications', hint: 'What emails you receive' },
        { href: '/submissions', icon: Music2, label: 'My submissions', hint: 'View your submitted music' },
        { href: '/logout', icon: LogOut, label: 'Sign out', hint: 'End your session', method: 'post' },
    ];

    const guestRows: Row[] = [
        { href: '/login', icon: KeyRound, label: 'Sign in', hint: 'Access your account' },
        { href: '/register', icon: UserCircle, label: 'Create an account', hint: 'Free — takes 30 seconds' },
    ];

    const legalRows: Row[] = [
        { href: '/about', icon: Music2, label: 'About', hint: 'What this platform is' },
        { href: '/contact', icon: Bell, label: 'Contact us', hint: 'Get in touch' },
        { href: '/terms', icon: Shield, label: 'Terms of Service', hint: 'How we work together' },
        { href: '/privacy', icon: Shield, label: 'Privacy Policy', hint: 'How we handle your data' },
        { href: '/copyright', icon: Shield, label: 'Copyright Policy', hint: 'How we handle rights' },
    ];

    return (
        <AppLayout title="Settings">
            <div className="max-w-2xl mx-auto space-y-8">
                <header>
                    <h1 className="text-2xl md:text-3xl font-bold text-ink">Settings</h1>
                    {isAuthed && (
                        <p className="text-sm text-slate-500 mt-1">
                            Signed in as <span className="text-ink font-medium">{auth.user!.email}</span>
                        </p>
                    )}
                </header>

                <SettingsGroup title={isAuthed ? 'Account' : 'Get started'}>
                    {(isAuthed ? authedRows : guestRows).map((row) => <SettingsRow key={row.label} row={row} />)}
                </SettingsGroup>

                <SettingsGroup title="About & legal">
                    {legalRows.map((row) => <SettingsRow key={row.label} row={row} />)}
                </SettingsGroup>
            </div>
        </AppLayout>
    );
}

function SettingsGroup({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <section>
            <h2 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 px-2">{title}</h2>
            <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                {children}
            </ul>
        </section>
    );
}

function SettingsRow({ row }: { row: Row }) {
    const Icon = row.icon;
    const content = (
        <span className="flex items-center gap-4 px-4 py-3.5 hover:bg-slate-50 transition-colors">
            <span className="h-9 w-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                <Icon className="h-4 w-4" />
            </span>
            <span className="flex-1 min-w-0">
                <span className="block text-sm font-medium text-ink">{row.label}</span>
                <span className="block text-xs text-slate-500">{row.hint}</span>
            </span>
            <ChevronRight className="h-4 w-4 text-slate-300 shrink-0" />
        </span>
    );

    return (
        <li>
            <Link href={row.href} method={row.method as any} as={row.method ? 'button' : undefined} className="block w-full text-left">
                {content}
            </Link>
        </li>
    );
}
