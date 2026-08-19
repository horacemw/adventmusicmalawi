import ApplicationLogo from '@/Components/ApplicationLogo';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div className="mb-6 text-center">
                <div className="flex justify-center mb-4">
                    <ApplicationLogo size="lg" iconOnly />
                </div>
                <h1 className="text-xl font-semibold text-ink">Welcome back</h1>
                <p className="text-sm text-slate-500 mt-1">Sign in to Malawi Adventist Music.</p>
            </div>

            {status && (
                <div className="mb-4 rounded-lg bg-brand-50 text-brand-800 text-sm px-3 py-2">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData(
                                    'remember',
                                    (e.target.checked || false) as false,
                                )
                            }
                        />
                        <span className="ms-2 text-sm text-slate-600">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="mt-6 flex items-center justify-between gap-3">
                    {canResetPassword ? (
                        <Link
                            href={route('password.request')}
                            className="text-sm text-slate-500 hover:text-brand-700"
                        >
                            Forgot your password?
                        </Link>
                    ) : <span />}

                    <PrimaryButton disabled={processing}>
                        Log in
                    </PrimaryButton>
                </div>

                <p className="mt-8 pt-6 border-t border-slate-100 text-sm text-center text-slate-500">
                    New here?{' '}
                    <Link href={route('register')} className="text-brand-700 hover:text-brand-800 font-semibold">
                        Create an account
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
