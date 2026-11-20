import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import { PageProps } from '@/types';
import { ProjectBoard, Workspace, WorkspaceOption } from '@/types/delox';
import { Link, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronRight,
    Menu,
    Plus,
    UserRound,
    X,
} from 'lucide-react';
import { PropsWithChildren, ReactNode, useEffect, useState } from 'react';

type ShellPageProps = PageProps<{
    workspaces?: Workspace[];
    currentWorkspace?: Workspace | null;
    workspace?: Pick<Workspace, 'id' | 'name' | 'slug'>;
    project?: ProjectBoard;
    canManageProjects?: boolean;
}>;

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const page = usePage<ShellPageProps>();
    const { auth } = page.props;
    const user = auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);
    const [realtimeConnected, setRealtimeConnected] = useState(false);

    const currentWorkspace =
        page.props.currentWorkspace ??
        page.props.workspace ??
        auth.workspaces.find(
            (workspace: WorkspaceOption) =>
                workspace.id === page.props.project?.workspace_id,
        ) ??
        auth.workspaces[0] ??
        null;
    const currentWorkspaceOption = currentWorkspace
        ? auth.workspaces.find(
              (workspace: WorkspaceOption) =>
                  workspace.id === currentWorkspace.id,
          )
        : null;
    const canManageProjects =
        page.props.canManageProjects ??
        ['owner', 'admin'].includes(currentWorkspaceOption?.pivot?.role ?? '');

    useEffect(() => {
        const connection = window.Echo?.connector.pusher.connection;

        if (!connection) {
            return;
        }

        const updateConnection = () => {
            setRealtimeConnected(connection.state === 'connected');
        };

        connection.bind('state_change', updateConnection);
        updateConnection();

        return () => {
            connection.unbind('state_change', updateConnection);
        };
    }, []);

    const initials = user.name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.slice(0, 1))
        .join('')
        .toUpperCase();

    return (
        <div className="min-h-screen bg-canvas text-ink-body">
            <nav className="sticky top-0 z-30 border-b border-border bg-white/90 backdrop-blur-md">
                <div className="mx-auto flex h-16 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div className="flex min-w-0 items-center gap-4">
                        <Link
                            href={route('dashboard')}
                            aria-label="delox workspace"
                            className="rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                        >
                            <ApplicationLogo className="h-10 w-32" />
                        </Link>

                        <div className="hidden h-7 w-px bg-border sm:block" />

                        <Dropdown>
                            <Dropdown.Trigger>
                                <button
                                    type="button"
                                    className="inline-flex max-w-52 items-center gap-2 rounded-xl border border-transparent px-3 py-2 text-sm font-semibold text-ink-body transition hover:border-sand/50 hover:bg-sand/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                                    aria-label="Switch workspace"
                                >
                                    <span className="flex size-6 shrink-0 items-center justify-center rounded-lg bg-sand/40 text-[10px] font-bold text-[#5a3626]">
                                        {currentWorkspace?.name
                                            .slice(0, 1)
                                            .toUpperCase() ?? 'D'}
                                    </span>
                                    <span className="truncate">
                                        {currentWorkspace?.name ?? 'Workspace'}
                                    </span>
                                    <ChevronDown
                                        size={15}
                                        className="shrink-0 text-ink-muted"
                                        aria-hidden="true"
                                    />
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content
                                width="48"
                                contentClasses="bg-white py-1"
                            >
                                <p className="px-4 pb-2 pt-3 text-[10px] font-bold uppercase tracking-[0.12em] text-ink-muted">
                                    Your workspaces
                                </p>
                                {auth.workspaces.map((workspace) => (
                                    <Dropdown.Link
                                        key={workspace.id}
                                        href={route('workspaces.show', {
                                            workspace: workspace.id,
                                        })}
                                        className="flex items-center gap-2.5"
                                    >
                                        <span className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-sand/30 text-[10px] font-bold text-[#5a3626]">
                                            {workspace.name
                                                .slice(0, 1)
                                                .toUpperCase()}
                                        </span>
                                        <span className="truncate">
                                            {workspace.name}
                                        </span>
                                    </Dropdown.Link>
                                ))}
                                <div className="my-1 border-t border-border" />
                                <Dropdown.Link href={route('dashboard')}>
                                    Manage workspaces
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>

                        <div className="hidden min-w-0 items-center gap-2 text-sm md:flex">
                            <ChevronRight
                                size={14}
                                className="shrink-0 text-ink-muted/60"
                                aria-hidden="true"
                            />
                            {page.props.project ? (
                                <>
                                    {currentWorkspace ? (
                                        <Link
                                            href={route('workspaces.show', {
                                                workspace:
                                                    currentWorkspace.id,
                                            })}
                                            className="truncate text-ink-muted transition hover:text-ink"
                                        >
                                            {currentWorkspace.name}
                                        </Link>
                                    ) : (
                                        <span className="truncate text-ink-muted">
                                            Workspace
                                        </span>
                                    )}
                                    <ChevronRight
                                        size={14}
                                        className="shrink-0 text-ink-muted/60"
                                        aria-hidden="true"
                                    />
                                    <span className="max-w-48 truncate font-semibold text-ink">
                                        {page.props.project.name}
                                    </span>
                                </>
                            ) : (
                                <span className="truncate font-semibold text-ink">
                                    {page.url.includes('/profile')
                                        ? 'Profile'
                                        : currentWorkspace?.name ?? 'Overview'}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="hidden shrink-0 items-center gap-3 sm:flex">
                        <span
                            className="inline-flex items-center gap-2 rounded-full bg-surface-subtle px-3 py-1.5 text-xs font-medium text-ink-muted"
                            aria-live="polite"
                        >
                            <span
                                className={`size-2 rounded-full ${
                                    realtimeConnected
                                        ? 'animate-pulse bg-sage'
                                        : 'bg-sand'
                                }`}
                            />
                            {realtimeConnected ? 'Live' : 'Offline'}
                        </span>
                        {canManageProjects && <Link
                            href={`${route('dashboard')}#new-project`}
                            className="inline-flex items-center gap-2 rounded-xl bg-sage px-3.5 py-2 text-sm font-semibold text-ink transition hover:bg-sage-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/60 focus-visible:ring-offset-2"
                        >
                            <Plus size={16} aria-hidden="true" />
                            New project
                        </Link>}
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button
                                    type="button"
                                    className="inline-flex items-center gap-2 rounded-xl p-1.5 pr-2.5 transition hover:bg-sand/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                                    aria-label="Open account menu"
                                >
                                    <span className="flex size-8 items-center justify-center rounded-full bg-sand/60 text-xs font-bold text-ink">
                                        {initials || <UserRound size={15} />}
                                    </span>
                                    <span className="max-w-28 truncate text-sm font-medium text-ink-body">
                                        {user.name}
                                    </span>
                                    <ChevronDown
                                        size={14}
                                        className="text-ink-muted"
                                        aria-hidden="true"
                                    />
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content contentClasses="bg-white py-1">
                                <Dropdown.Link href={route('profile.edit')}>
                                    Profile settings
                                </Dropdown.Link>
                                <Dropdown.Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                >
                                    Sign out
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>

                    <button
                        type="button"
                        onClick={() =>
                            setShowingNavigationDropdown((previous) => !previous)
                        }
                        className="inline-flex items-center justify-center rounded-xl p-2 text-ink-body transition hover:bg-sand/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage sm:hidden"
                        aria-label={
                            showingNavigationDropdown
                                ? 'Close navigation'
                                : 'Open navigation'
                        }
                        aria-expanded={showingNavigationDropdown}
                    >
                        {showingNavigationDropdown ? (
                            <X size={20} aria-hidden="true" />
                        ) : (
                            <Menu size={20} aria-hidden="true" />
                        )}
                    </button>
                </div>

                {showingNavigationDropdown && (
                    <div className="border-t border-border bg-white px-4 py-4 sm:hidden">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <span className="text-sm font-semibold text-ink">
                                {currentWorkspace?.name ?? 'Your workspace'}
                            </span>
                            <span className="inline-flex items-center gap-2 text-xs text-ink-muted">
                                <span
                                    className={`size-2 rounded-full ${realtimeConnected ? 'animate-pulse bg-sage' : 'bg-sand'}`}
                                />
                                {realtimeConnected ? 'Live' : 'Offline'}
                            </span>
                        </div>
                        <div className="grid gap-1">
                            <Link
                                href={route('dashboard')}
                                className="rounded-xl px-3 py-2.5 text-sm font-medium text-ink-body hover:bg-sage/15"
                            >
                                Workspace overview
                            </Link>
                            {canManageProjects && <Link
                                href={`${route('dashboard')}#new-project`}
                                className="rounded-xl px-3 py-2.5 text-sm font-medium text-ink-body hover:bg-sage/15"
                            >
                                New project
                            </Link>}
                            <Link
                                href={route('profile.edit')}
                                className="rounded-xl px-3 py-2.5 text-sm font-medium text-ink-body hover:bg-sand/15"
                            >
                                Profile settings
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="rounded-xl px-3 py-2.5 text-start text-sm font-medium text-ink-muted hover:bg-sand/15 hover:text-ink-body"
                            >
                                Sign out
                            </Link>
                        </div>
                    </div>
                )}
            </nav>

            {header && (
                <header className="border-b border-border bg-white/70">
                    <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {children}
        </div>
    );
}
