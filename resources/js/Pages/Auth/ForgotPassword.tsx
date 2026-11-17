import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot Password" />

            <div className="mb-6">
                <h1 className="text-2xl font-semibold tracking-tight text-ink">
                    Forgot your password?
                </h1>
                <p className="mt-2 text-sm leading-6 text-ink-muted">
                    No problem. Enter your email address and we will send you a
                    link to choose a new password.
                </p>
            </div>

            {status && (
                <div className="mb-4 rounded-xl bg-sage/20 px-4 py-3 text-sm font-medium text-[#25443c]">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <label
                    htmlFor="email"
                    className="mb-1.5 block text-sm font-semibold text-ink-body"
                >
                    Email address
                </label>
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    value={data.email}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={(e) => setData('email', e.target.value)}
                />

                <InputError message={errors.email} className="mt-2" />

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Email Password Reset Link
                    </PrimaryButton>
                </div>
            </form>

            <div className="mt-6 border-t border-border pt-5 text-center">
                <Link
                    href={route('login')}
                    className="text-sm font-semibold text-ink-body underline decoration-sand decoration-2 underline-offset-4 hover:text-ink"
                >
                    Back to sign in
                </Link>
            </div>
        </GuestLayout>
    );
}
