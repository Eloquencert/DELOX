import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-canvas px-4 py-12">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -left-36 -top-40 size-96 rounded-full bg-sage/20 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -bottom-40 -right-36 size-96 rounded-full bg-sand/25 blur-3xl"
            />
            <main className="relative w-full max-w-md">
                <Link href="/">
                    <ApplicationLogo className="mx-auto h-16 w-40" />
                </Link>

                <div className="mt-8 w-full overflow-hidden rounded-2xl border border-border bg-surface p-8 shadow-[0_2px_12px_rgba(31,36,33,0.04)]">
                    {children}
                </div>
                <p className="mt-6 text-center text-xs text-ink-muted">
                    A little more focus. A lot more momentum.
                </p>
            </main>
        </div>
    );
}
