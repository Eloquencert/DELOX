import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Workspace } from '@/types/delox';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowUpRight, FolderKanban, Plus, Trash2, Users } from 'lucide-react';
import { FormEvent, useRef } from 'react';

interface DashboardProps extends PageProps {
    workspaces: Workspace[];
    currentWorkspace: Workspace | null;
    canDeleteWorkspace: boolean;
    canManageMembers: boolean;
    canManageProjects: boolean;
}

export default function Dashboard({
    auth,
    workspaces,
    currentWorkspace,
    canDeleteWorkspace,
    canManageMembers,
    canManageProjects,
}: DashboardProps) {
    const workspaceForm = useForm({ name: '' });
    const projectForm = useForm({ name: '', key: '', description: '' });
    const projectFormRef = useRef<HTMLFormElement>(null);

    const createWorkspace = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        workspaceForm.post(route('workspaces.store'), {
            onSuccess: () => workspaceForm.reset(),
        });
    };

    const createProject = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!currentWorkspace) {
            return;
        }

        projectForm.transform((data) => ({
            ...data,
            key: data.key.trim().toUpperCase(),
        }));
        projectForm.post(
            route('projects.store', { workspace: currentWorkspace.id }),
            { onSuccess: () => projectForm.reset() },
        );
    };

    const deleteWorkspace = () => {
        if (
            !currentWorkspace ||
            !window.confirm(
                `Delete "${currentWorkspace.name}" and all of its projects? This cannot be undone.`,
            )
        ) {
            return;
        }

        router.delete(
            route('workspaces.destroy', { workspace: currentWorkspace.id }),
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title="Workspace" />

            <main className="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:px-8">
                <aside className="space-y-5">
                    <section>
                        <div className="mb-2 flex items-center justify-between px-2">
                            <p className="text-xs font-bold uppercase tracking-[0.12em] text-ink-muted">
                                Workspaces
                            </p>
                            <span className="rounded-full bg-sand/30 px-2 py-0.5 text-[10px] font-semibold tabular-nums text-[#5a3626]">
                                {workspaces.length}
                            </span>
                        </div>
                        <nav aria-label="Workspaces" className="grid gap-1">
                            {workspaces.map((workspace) => (
                                <Link
                                    key={workspace.id}
                                    href={route('workspaces.show', {
                                        workspace: workspace.id,
                                    })}
                                    className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${currentWorkspace?.id === workspace.id
                                            ? 'bg-sage/25 text-ink'
                                            : 'text-ink-muted hover:bg-sand/15 hover:text-ink-body'
                                        }`}
                                >
                                    <span className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-white text-[11px] font-bold text-ink shadow-sm">
                                        {workspace.name.slice(0, 1).toUpperCase()}
                                    </span>
                                    <span className="truncate">
                                        {workspace.name}
                                    </span>
                                </Link>
                            ))}
                        </nav>
                    </section>

                    <form
                        onSubmit={createWorkspace}
                        className="space-y-3 rounded-2xl border border-border bg-white p-4 shadow-[0_2px_12px_rgba(31,36,33,0.04)]"
                    >
                        <div>
                            <h2 className="text-sm font-semibold text-ink">
                                New workspace
                            </h2>
                            <p className="mt-1 text-xs leading-5 text-ink-muted">
                                A shared home for your projects and people.
                            </p>
                        </div>
                        <label htmlFor="workspace-name" className="sr-only">
                            Workspace name
                        </label>
                        <input
                            id="workspace-name"
                            value={workspaceForm.data.name}
                            onChange={(event) =>
                                workspaceForm.setData('name', event.target.value)
                            }
                            maxLength={255}
                            placeholder="Workspace name"
                            className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                            required
                        />
                        {workspaceForm.errors.name && (
                            <p role="alert" className="text-xs text-[#9f4633]">
                                {workspaceForm.errors.name}
                            </p>
                        )}
                        <button
                            type="submit"
                            disabled={workspaceForm.processing}
                            className="w-full rounded-xl border border-border px-3 py-2.5 text-sm font-semibold text-ink-body transition hover:border-sage hover:bg-sage/10 disabled:opacity-50"
                        >
                            Create workspace
                        </button>
                    </form>
                </aside>

                <section className="min-w-0">
                    {currentWorkspace ? (
                        <>
                            <header className="mb-8 flex flex-wrap items-end justify-between gap-5">
                                <div>
                                    <p className="text-sm font-medium text-ink-muted">
                                        Good to see you,{' '}
                                        {auth.user.name.split(' ')[0]}
                                    </p>
                                    <h1 className="mt-1 text-3xl font-semibold tracking-tight text-ink">
                                        {currentWorkspace.name}
                                    </h1>
                                    <p className="mt-2 text-sm text-ink-muted">
                                        Your team’s projects, all in one place.
                                    </p>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    {canManageMembers && <Link
                                        href={route('workspaces.members', {
                                            workspace: currentWorkspace.id,
                                        })}
                                        className="inline-flex items-center gap-2 rounded-xl border border-sand/60 bg-sand/20 px-3.5 py-2.5 text-sm font-semibold text-[#5a3626] transition hover:bg-sand/35 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sand"
                                    >
                                        <Users size={16} aria-hidden="true" />
                                        <span>
                                            {currentWorkspace.users_count ?? 0}{' '}
                                            members
                                        </span>
                                        <span className="hidden sm:inline">
                                            · Invite
                                        </span>
                                    </Link>}
                                    {canDeleteWorkspace && (
                                        <button
                                            type="button"
                                            onClick={deleteWorkspace}
                                            className="inline-flex items-center gap-2 rounded-xl border border-[#c96a50]/30 px-3.5 py-2.5 text-sm font-semibold text-[#9f4633] transition hover:bg-[#c96a50]/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#c96a50]/30"
                                        >
                                            <Trash2 size={16} aria-hidden="true" />
                                            Delete workspace
                                        </button>
                                    )}
                                </div>
                            </header>

                            <div className="mb-8 flex items-center justify-between">
                                <div>
                                    <h2 className="text-lg font-semibold text-ink">
                                        Projects
                                    </h2>
                                    <p className="mt-1 text-sm text-ink-muted">
                                        Pick up where your team left off.
                                    </p>
                                </div>
                            </div>

                            {currentWorkspace.projects.length > 0 ? (
                                <div className="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    {currentWorkspace.projects.map((project) => (
                                        <Link
                                            key={project.id}
                                            href={route('projects.board', {
                                                project: project.id,
                                            })}
                                            className="group rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] transition-all hover:-translate-y-0.5 hover:border-sage hover:shadow-[0_10px_28px_rgba(31,36,33,0.08)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <span className="rounded-lg bg-sand/30 px-2.5 py-1 font-mono text-xs font-semibold tabular-nums text-[#5a3626]">
                                                    {project.key}
                                                </span>
                                                <ArrowUpRight
                                                    size={17}
                                                    className="text-ink-muted transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-ink"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <h3 className="mt-4 text-base font-semibold text-ink">
                                                {project.name}
                                            </h3>
                                            <p className="mt-2 min-h-10 text-sm leading-5 text-ink-muted">
                                                {project.description ||
                                                    'A focused space to organize work and keep momentum.'}
                                            </p>
                                            <div className="mt-5 flex items-center justify-between border-t border-border pt-4">
                                                <span className="text-xs font-medium tabular-nums text-ink-muted">
                                                    {project.tasks_count ?? 0}{' '}
                                                    tasks
                                                </span>
                                                <div
                                                    className="flex items-center"
                                                    aria-label={`${currentWorkspace.users_count ?? currentWorkspace.users?.length ?? 0} workspace members`}
                                                >
                                                    {currentWorkspace.users
                                                        ?.slice(0, 4)
                                                        .map((member, index) => (
                                                            <span
                                                                key={member.id}
                                                                title={
                                                                    member.name
                                                                }
                                                                className={`-ml-2 flex size-7 items-center justify-center rounded-full border-2 border-white text-[10px] font-bold text-ink first:ml-0 ${index % 2 ===
                                                                        0
                                                                        ? 'bg-sage/60'
                                                                        : 'bg-sand/70'
                                                                    }`}
                                                            >
                                                                {member.name
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </span>
                                                        ))}
                                                    {(currentWorkspace.users
                                                        ?.length ?? 0) > 4 && (
                                                            <span className="-ml-2 flex size-7 items-center justify-center rounded-full border-2 border-white bg-canvas text-[9px] font-semibold text-ink-muted">
                                                                +
                                                                {(currentWorkspace
                                                                    .users?.length ??
                                                                    0) - 4}
                                                            </span>
                                                        )}
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <div className="mb-8 rounded-2xl border border-dashed border-sage/70 bg-white/70 px-6 py-10 text-center">
                                    <span className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-sage/25 text-[#41675d]">
                                        <FolderKanban
                                            size={22}
                                            aria-hidden="true"
                                        />
                                    </span>
                                    <h3 className="mt-4 text-base font-semibold text-ink">
                                        {canManageProjects
                                            ? 'Your first project starts here'
                                            : 'No projects yet'}
                                    </h3>
                                    <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-ink-muted">
                                        {canManageProjects
                                            ? 'Create a project to give your team a clear place for tasks, ideas, and progress.'
                                            : 'Projects created for this workspace will appear here.'}
                                    </p>
                                    {canManageProjects && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                projectFormRef.current?.scrollIntoView(
                                                    {
                                                        behavior: 'smooth',
                                                        block: 'center',
                                                    },
                                                )
                                            }
                                            className="mt-5 inline-flex items-center gap-2 rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover"
                                        >
                                            <Plus size={16} aria-hidden="true" />
                                            Create first project
                                        </button>
                                    )}
                                </div>
                            )}

                            {canManageProjects && (
                                <form
                                    id="new-project"
                                    ref={projectFormRef}
                                    onSubmit={createProject}
                                    className="space-y-5 rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] sm:p-6"
                                >
                                <div>
                                    <h2 className="text-base font-semibold text-ink">
                                        Create a project
                                    </h2>
                                    <p className="mt-1 text-sm text-ink-muted">
                                        Projects start with a four-column board.
                                    </p>
                                </div>
                                <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_120px]">
                                    <label className="block text-sm font-semibold text-ink-body">
                                        Name
                                        <input
                                            value={projectForm.data.name}
                                            onChange={(event) =>
                                                projectForm.setData(
                                                    'name',
                                                    event.target.value,
                                                )
                                            }
                                            maxLength={255}
                                            required
                                            className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                        />
                                        {projectForm.errors.name && (
                                            <span className="mt-1 block text-xs text-[#9f4633]">
                                                {projectForm.errors.name}
                                            </span>
                                        )}
                                    </label>
                                    <label className="block text-sm font-semibold text-ink-body">
                                        Key
                                        <input
                                            value={projectForm.data.key}
                                            onChange={(event) =>
                                                projectForm.setData(
                                                    'key',
                                                    event.target.value
                                                        .toUpperCase()
                                                        .slice(0, 6),
                                                )
                                            }
                                            maxLength={6}
                                            required
                                            className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] font-mono uppercase tabular-nums text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                        />
                                        {projectForm.errors.key && (
                                            <span className="mt-1 block text-xs text-[#9f4633]">
                                                {projectForm.errors.key}
                                            </span>
                                        )}
                                    </label>
                                </div>
                                <label className="block text-sm font-semibold text-ink-body">
                                    Description
                                    <textarea
                                        value={projectForm.data.description}
                                        onChange={(event) =>
                                            projectForm.setData(
                                                'description',
                                                event.target.value,
                                            )
                                        }
                                        rows={3}
                                        maxLength={10000}
                                        className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                    />
                                </label>
                                {projectForm.errors.description && (
                                    <p role="alert" className="text-sm text-[#9f4633]">
                                        {projectForm.errors.description}
                                    </p>
                                )}
                                <button
                                    type="submit"
                                    disabled={projectForm.processing}
                                    className="inline-flex items-center gap-2 rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Plus size={16} aria-hidden="true" />
                                    {projectForm.processing
                                        ? 'Creating...'
                                        : 'Create project'}
                                </button>
                                </form>
                            )}
                        </>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-sage/70 bg-white/70 p-10 text-center">
                            <span className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-sage/25 text-[#41675d]">
                                <Users size={22} aria-hidden="true" />
                            </span>
                            <h1 className="mt-4 text-xl font-semibold text-ink">
                                Create your first workspace
                            </h1>
                            <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-ink-muted">
                                Workspaces keep your projects and teammates
                                together. Add a name in the form to get started.
                            </p>
                        </div>
                    )}
                </section>
            </main>
        </AuthenticatedLayout>
    );
}
