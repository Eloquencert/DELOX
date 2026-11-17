import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    Check,
    Circle,
    Clock3,
    MessageSquare,
    Plus,
    Search,
    Users,
    Zap,
} from 'lucide-react';

const previewTasks = [
    { title: 'Shape the next release', priority: 'High', accent: 'sand' },
    { title: 'Review onboarding notes', priority: 'Medium', accent: 'sage' },
    { title: 'Share progress with the team', priority: 'Low', accent: 'stone' },
];

export default function Welcome() {
    return (
        <>
            <Head title="A calmer way to work" />
            <div className="relative min-h-screen overflow-hidden bg-canvas text-ink">
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -right-36 -top-44 size-[30rem] rounded-full bg-sage/20 blur-3xl"
                />
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -bottom-48 -left-40 size-[30rem] rounded-full bg-sand/20 blur-3xl"
                />

                <div className="relative mx-auto max-w-7xl px-5 sm:px-8">
                    <header className="flex h-20 items-center justify-between border-b border-border/80">
                        <Link
                            href="/"
                            aria-label="delox home"
                            className="rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                        >
                            <ApplicationLogo className="h-10 w-32" />
                        </Link>
                        <nav
                            aria-label="Main navigation"
                            className="flex items-center gap-2 sm:gap-3"
                        >
                            <Link
                                href={route('login')}
                                className="rounded-xl px-4 py-2.5 text-sm font-semibold text-ink-body transition hover:bg-sand/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                            >
                                Sign in
                            </Link>
                            <Link
                                href={route('register')}
                                className="inline-flex items-center gap-2 rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/60 focus-visible:ring-offset-2"
                            >
                                Get started
                                <ArrowRight size={16} aria-hidden="true" />
                            </Link>
                        </nav>
                    </header>

                    <main>
                        <section className="grid items-center gap-14 py-16 sm:py-20 lg:grid-cols-[0.9fr_1.1fr] lg:gap-12 lg:py-28">
                            <div className="max-w-xl">
                                <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-sage/40 bg-white/70 px-3.5 py-2 text-xs font-semibold tracking-wide text-ink-body shadow-sm">
                                    <span className="size-2 rounded-full bg-sage" />
                                    A calmer workspace for focused teams
                                </div>
                                <h1 className="text-4xl font-semibold leading-[1.08] tracking-[-0.045em] text-ink sm:text-5xl lg:text-6xl">
                                    Make room for your team’s{' '}
                                    <span className="relative whitespace-nowrap">
                                        best work.
                                        <span
                                            aria-hidden="true"
                                            className="absolute -bottom-1 left-0 -z-10 h-3 w-full rounded-full bg-sand/50"
                                        />
                                    </span>
                                </h1>
                                <p className="mt-6 max-w-lg text-base leading-7 text-ink-muted sm:text-lg">
                                    Keep projects clear, conversations close,
                                    and progress moving. delox brings your
                                    team’s work together without adding noise.
                                </p>
                                <div className="mt-8 flex flex-wrap items-center gap-3">
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center gap-2 rounded-xl bg-sage px-5 py-3 text-sm font-semibold text-ink shadow-[0_8px_24px_rgba(157,190,181,0.28)] transition hover:-translate-y-0.5 hover:bg-sage-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/60 focus-visible:ring-offset-2"
                                    >
                                        Create your workspace
                                        <ArrowRight
                                            size={16}
                                            aria-hidden="true"
                                        />
                                    </Link>
                                    <span className="inline-flex items-center gap-2 px-2 text-sm text-ink-muted">
                                        <Check
                                            size={15}
                                            className="text-[#557c70]"
                                            aria-hidden="true"
                                        />
                                        Simple to get started
                                    </span>
                                </div>
                                <div className="mt-12 flex flex-wrap gap-x-6 gap-y-3 border-t border-border/80 pt-6 text-sm text-ink-muted">
                                    <span className="inline-flex items-center gap-2">
                                        <Users
                                            size={16}
                                            className="text-ink-body"
                                            aria-hidden="true"
                                        />
                                        Made for teams
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <Zap
                                            size={16}
                                            className="text-ink-body"
                                            aria-hidden="true"
                                        />
                                        Updates in real time
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <Clock3
                                            size={16}
                                            className="text-ink-body"
                                            aria-hidden="true"
                                        />
                                        Less busywork
                                    </span>
                                </div>
                            </div>

                            <div className="relative mx-auto w-full max-w-2xl">
                                <div
                                    aria-hidden="true"
                                    className="absolute -left-6 top-14 size-20 rounded-[1.75rem] bg-sand/60 rotate-[-12deg]"
                                />
                                <div
                                    aria-hidden="true"
                                    className="absolute -right-5 bottom-12 size-24 rounded-full bg-sage/50"
                                />
                                <div className="relative overflow-hidden rounded-[1.75rem] border border-border bg-white p-3 shadow-[0_24px_70px_rgba(31,36,33,0.12)] sm:p-5">
                                    <div className="flex items-center justify-between gap-4 border-b border-border pb-4">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-sage/25 text-ink">
                                                <Circle
                                                    size={17}
                                                    strokeWidth={2.5}
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-ink">
                                                    Product launch
                                                </p>
                                                <p className="text-xs text-ink-muted">
                                                    Workspace / Projects
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button
                                                type="button"
                                                aria-label="Search preview"
                                                className="hidden rounded-lg border border-border p-2 text-ink-muted sm:block"
                                            >
                                                <Search
                                                    size={15}
                                                    aria-hidden="true"
                                                />
                                            </button>
                                            <div className="flex -space-x-2">
                                                {['M', 'A', 'J'].map(
                                                    (initial, index) => (
                                                        <span
                                                            key={initial}
                                                            className={`flex size-8 items-center justify-center rounded-full border-2 border-white text-[10px] font-bold text-ink ${
                                                                index === 1
                                                                    ? 'bg-sage'
                                                                    : 'bg-sand'
                                                            }`}
                                                        >
                                                            {initial}
                                                        </span>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid gap-3 py-4 sm:grid-cols-3">
                                        {[
                                            {
                                                name: 'Up next',
                                                count: 3,
                                                tasks: previewTasks.slice(0, 2),
                                            },
                                            {
                                                name: 'In progress',
                                                count: 2,
                                                tasks: previewTasks.slice(1, 3),
                                            },
                                            {
                                                name: 'Done',
                                                count: 4,
                                                tasks: previewTasks.slice(2, 3),
                                            },
                                        ].map((column) => (
                                            <div
                                                key={column.name}
                                                className="rounded-2xl border border-border-subtle bg-surface-subtle p-2.5"
                                            >
                                                <div className="mb-2 flex items-center justify-between px-1">
                                                    <h2 className="text-xs font-semibold text-ink-body">
                                                        {column.name}
                                                    </h2>
                                                    <span className="rounded-full bg-canvas px-2 py-0.5 text-[10px] tabular-nums text-ink-muted">
                                                        {column.count}
                                                    </span>
                                                </div>
                                                <div className="grid gap-2">
                                                    {column.tasks.map((task) => (
                                                        <article
                                                            key={`${column.name}-${task.title}`}
                                                            className="rounded-xl border border-border-subtle bg-white p-3 shadow-[0_1px_4px_rgba(31,36,33,0.03)]"
                                                        >
                                                            <p className="mb-2 text-[10px] font-medium tabular-nums text-ink-muted">
                                                                DLX-12
                                                            </p>
                                                            <p className="text-xs font-semibold leading-5 text-ink">
                                                                {task.title}
                                                            </p>
                                                            <div className="mt-3 flex items-center justify-between gap-2">
                                                                <span
                                                                    className={`rounded-full border px-2 py-0.5 text-[9px] font-semibold ${
                                                                        task.accent ===
                                                                        'sand'
                                                                            ? 'border-sand/70 bg-sand/35 text-[#5a3626]'
                                                                            : task.accent ===
                                                                                'sage'
                                                                              ? 'border-sage/50 bg-sage/25 text-[#25443c]'
                                                                              : 'border-border bg-canvas text-ink-body'
                                                                    }`}
                                                                >
                                                                    {
                                                                        task.priority
                                                                    }
                                                                </span>
                                                                <span className="flex size-5 items-center justify-center rounded-full bg-sand/60 text-[8px] font-bold text-ink">
                                                                    M
                                                                </span>
                                                            </div>
                                                        </article>
                                                    ))}
                                                </div>
                                                <div className="mt-2 flex items-center gap-1 px-1 text-[10px] font-medium text-ink-muted">
                                                    <Plus
                                                        size={12}
                                                        aria-hidden="true"
                                                    />
                                                    Add task
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex items-center justify-between rounded-xl bg-canvas/80 px-3 py-2.5 text-xs text-ink-muted">
                                        <span className="inline-flex items-center gap-2">
                                            <span className="size-2 rounded-full bg-sage" />
                                            Everyone is up to date
                                        </span>
                                        <span className="inline-flex items-center gap-1">
                                            <MessageSquare
                                                size={13}
                                                aria-hidden="true"
                                            />
                                            Shared context
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="border-t border-border/80 py-8 text-center">
                            <p className="text-sm text-ink-muted">
                                A little more focus. A lot more momentum.
                            </p>
                        </section>
                    </main>
                </div>
            </div>
        </>
    );
}
