import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-ink">
                    Account settings
                </h2>
            }
        >
            <Head title="Profile" />

            <main className="bg-canvas py-10">
                <div className="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
                    <section className="rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] sm:p-8">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </section>

                    <section className="rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] sm:p-8">
                        <UpdatePasswordForm className="max-w-xl" />
                    </section>

                    <section className="rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] sm:p-8">
                        <DeleteUserForm className="max-w-xl" />
                    </section>
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
