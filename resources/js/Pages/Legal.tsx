import AppLayout from '@/Layouts/AppLayout';
import type { ReactNode } from 'react';

interface Props {
    kind: 'terms' | 'privacy' | 'copyright';
}

const CONTENT: Record<Props['kind'], { title: string; updated: string; body: ReactNode }> = {
    terms: {
        title: 'Terms of Service',
        updated: '19 August 2026',
        body: (
            <>
                <p>These terms govern your use of Malawi Adventist Music (the "platform"). By creating an account or streaming from the platform you agree to these terms.</p>

                <h2>Who we are</h2>
                <p>Malawi Adventist Music is a music streaming platform for Seventh-day Adventist music in Malawi. Listening is free; artists and groups may pay to submit music for review and publication.</p>

                <h2>Your account</h2>
                <ul>
                    <li>You must be at least 13 years old to create an account.</li>
                    <li>You are responsible for keeping your password confidential. Notify us at hello@malawiadventistmusic.com if you suspect unauthorised access.</li>
                    <li>You may not use the platform to break the law, harass others, distribute copyrighted material without permission, or attempt to bypass our security.</li>
                </ul>

                <h2>Music submissions</h2>
                <ul>
                    <li>Submitters must own the recording, hold a valid licence, or have written permission from the rights holder to distribute it.</li>
                    <li>Every submission is reviewed by our moderators before publication.</li>
                    <li>The submission fee is non-refundable in the general case. See the Refund section for exceptions.</li>
                    <li>We may reject any submission at our discretion. Rejection reasons include unclear copyright, poor audio quality, mismatched metadata, or content unrelated to Adventist music.</li>
                </ul>

                <h2>Refunds</h2>
                <p>We refund the submission fee if we reject a submission for reasons that are our fault (a platform bug, moderator error, etc.). We do not refund submissions rejected for copyright issues, incorrect metadata that the submitter could have corrected, or content policy violations.</p>

                <h2>Streaming</h2>
                <p>Streaming is free and intended for personal, non-commercial listening. You may not re-broadcast our streams to a public audience without permission from the rights holder of each song.</p>

                <h2>Content ownership</h2>
                <p>Rights holders retain full ownership of their music. Submitting music to the platform grants us a non-exclusive licence to stream it on our web and mobile apps, and to promote it within the platform. You can withdraw a song at any time — email us and we'll unpublish it.</p>

                <h2>Termination</h2>
                <p>We can suspend or terminate any account that violates these terms. You can close your account at any time by emailing us.</p>

                <h2>Changes</h2>
                <p>We may update these terms. We'll notify you by email of significant changes.</p>
            </>
        ),
    },
    privacy: {
        title: 'Privacy Policy',
        updated: '19 August 2026',
        body: (
            <>
                <p>This policy explains what we collect, why we collect it, and how you can control it.</p>

                <h2>What we collect</h2>
                <ul>
                    <li><strong>Account data:</strong> name, email address, phone (optional), password hash.</li>
                    <li><strong>Streaming activity:</strong> songs you play, duration, device type, and a hashed IP for anti-abuse. We do not store your raw IP address.</li>
                    <li><strong>Submission data:</strong> if you submit music, we store the metadata, uploaded files, and payment records.</li>
                    <li><strong>Cookies:</strong> a session cookie to keep you logged in, plus an XSRF cookie for form security. No third-party trackers.</li>
                </ul>

                <h2>How we use it</h2>
                <ul>
                    <li>Show you the platform, recommend music, remember your playlists and likes.</li>
                    <li>Send transactional email (verification, password reset, submission and payment updates).</li>
                    <li>Compute aggregate stats (plays per song, popular categories) — never individually identifying.</li>
                    <li>Detect and prevent abuse.</li>
                </ul>

                <h2>Who we share with</h2>
                <ul>
                    <li><strong>PayChangu</strong> — payment processing for music submissions.</li>
                    <li><strong>Resend</strong> — email delivery for platform notifications.</li>
                    <li><strong>Hetzner Cloud</strong> — server hosting in Germany.</li>
                </ul>
                <p>We do not sell your data to advertisers.</p>

                <h2>Your rights</h2>
                <ul>
                    <li>See what we have on you — email hello@malawiadventistmusic.com.</li>
                    <li>Update or delete your account — via your account settings or by emailing us.</li>
                    <li>Withdraw submitted music — email us and we'll unpublish it.</li>
                </ul>

                <h2>Retention</h2>
                <p>Account data is kept while your account is active. Streaming logs are kept for 12 months in raw form, then aggregated. Payment records are kept for 7 years for legal / tax reasons.</p>

                <h2>Contact</h2>
                <p>Privacy questions: hello@malawiadventistmusic.com.</p>
            </>
        ),
    },
    copyright: {
        title: 'Copyright Policy',
        updated: '19 August 2026',
        body: (
            <>
                <p>Hymns and church music include material with real copyright holders. We treat copyright seriously.</p>

                <h2>What submitters affirm</h2>
                <p>Every submission requires the submitter to confirm all three of the following:</p>
                <ul>
                    <li>They own the recording, hold a valid licence, or have written permission from the rights holder to distribute it.</li>
                    <li>They grant Malawi Adventist Music permission to stream the recording on our platform.</li>
                    <li>The information they submitted is accurate to the best of their knowledge.</li>
                </ul>
                <p>Supporting documentation (a licence, permission email, or written authorisation) can be uploaded as part of the submission.</p>

                <h2>Hymn books</h2>
                <p>Hymn books are not automatically public domain. Even if the underlying hymn is old, the specific published edition may still be under copyright. Every hymn book on the platform has its own licence and copyright metadata.</p>

                <h2>Reporting copyright infringement</h2>
                <p>If you believe your work is being used without permission, please tell us. Send an email to copyright@malawiadventistmusic.com with:</p>
                <ul>
                    <li>Your name, organisation, and contact details.</li>
                    <li>Identification of the work you own (song title, recording, or hymn).</li>
                    <li>The URL of the material on our platform.</li>
                    <li>A statement that you have a good-faith belief the use is not authorised.</li>
                    <li>A statement, under penalty of perjury, that the information is accurate and you are the rights holder or authorised to act on their behalf.</li>
                </ul>

                <h2>What happens after a report</h2>
                <ol>
                    <li>We acknowledge receipt within 2 business days.</li>
                    <li>A moderator reviews the claim. We may suspend the targeted song from public streaming while the claim is under review.</li>
                    <li>We contact the submitter to hear their side.</li>
                    <li>We reach a decision and record it in our audit log. Resolutions include: dismiss, unpublish permanently, or update copyright metadata.</li>
                </ol>

                <h2>Repeat offenders</h2>
                <p>Accounts that repeatedly submit infringing material will be suspended and, in serious cases, terminated. Payment refunds are handled per our terms.</p>
            </>
        ),
    },
};

export default function Legal({ kind }: Props) {
    const c = CONTENT[kind];
    return (
        <AppLayout title={c.title}>
            <article className="max-w-3xl mx-auto rounded-3xl bg-white border border-slate-200 shadow-card p-6 md:p-10">
                <p className="text-xs font-semibold uppercase tracking-widest text-brand-700 mb-2">
                    Legal
                </p>
                <h1 className="text-3xl md:text-4xl font-bold text-ink mb-1">{c.title}</h1>
                <p className="text-xs text-slate-500 mb-8">Last updated {c.updated}</p>
                <div className="legal-body space-y-4 text-sm md:text-base text-slate-700 leading-relaxed">
                    {c.body}
                </div>
            </article>

            <style>{`
                .legal-body h2 {
                    margin-top: 2rem;
                    margin-bottom: 0.75rem;
                    font-size: 1.125rem;
                    font-weight: 600;
                    color: rgb(15 23 42);
                }
                .legal-body ul, .legal-body ol {
                    padding-left: 1.5rem;
                    margin: 0.5rem 0 0.75rem;
                }
                .legal-body ul { list-style-type: disc; }
                .legal-body ol { list-style-type: decimal; }
                .legal-body li { margin: 0.375rem 0; }
                .legal-body p { color: rgb(71 85 105); }
                .legal-body strong { color: rgb(15 23 42); font-weight: 600; }
                .legal-body a { color: rgb(21 128 61); }
                .legal-body a:hover { color: rgb(22 101 52); text-decoration: underline; }
            `}</style>
        </AppLayout>
    );
}
