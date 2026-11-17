import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { WorkspaceMember, WorkspaceRole } from '@/types/delox';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';

interface MemberPageProps extends PageProps {
    workspace: {
        id: number;
        name: string;
    };
    members: WorkspaceMember[];
    canManageMembers: boolean;
}

function MemberRow({
    workspaceId,
    member,
    canManageMembers,
}: {
    workspaceId: number;
    member: WorkspaceMember;
    canManageMembers: boolean;
}) {
    const [role, setRole] = useState<WorkspaceRole>(member.pivot.role);
    const [updating, setUpdating] = useState(false);

    useEffect(() => {
        setRole(member.pivot.role);
    }, [member.pivot.role]);

    const updateRole = (role: WorkspaceRole) => {
        setRole(role);
        router.patch(
            route('workspaces.members.update', {
                workspace: workspaceId,
                user: member.id,
            }),
            { role },
            {
                preserveScroll: true,
                onStart: () => setUpdating(true),
                onFinish: () => setUpdating(false),
            },
        );
    };

    const removeMember = () => {
        if (!window.confirm(`Remove ${member.name} from this workspace?`)) {
            return;
        }

        router.delete(
            route('workspaces.members.destroy', {
                workspace: workspaceId,
                user: member.id,
            }),
            { preserveScroll: true },
        );
    };

    return (
        <li className="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
            <div className="min-w-0">
                <p className="truncate text-sm font-semibold text-ink">
                    {member.name}
                </p>
                <p className="truncate text-sm text-ink-muted">{member.email}</p>
            </div>
            <div className="flex items-center gap-3">
                {canManageMembers && member.pivot.role !== 'owner' ? (
                    <label className="sr-only" htmlFor={`role-${member.id}`}>
                        Role for {member.name}
                    </label>
                ) : null}
                {canManageMembers && member.pivot.role !== 'owner' ? (
                    <select
                        id={`role-${member.id}`}
                        value={role}
                        onChange={(event) =>
                            updateRole(event.target.value as WorkspaceRole)
                        }
                        disabled={updating}
                        className="rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-sage-hover focus:ring-sage/40"
                    >
                        <option value="admin">Admin</option>
                        <option value="member">Member</option>
                    </select>
                ) : (
                    <span className="rounded-full bg-sand/30 px-2.5 py-1 text-xs font-semibold capitalize text-[#5a3626]">
                        {member.pivot.role}
                    </span>
                )}
                {canManageMembers && member.pivot.role !== 'owner' && (
                    <button
                        type="button"
                        onClick={removeMember}
                        className="rounded-xl px-2.5 py-1.5 text-sm font-semibold text-[#9f4633] transition hover:bg-[#c96a50]/10"
                    >
                        Remove
                    </button>
                )}
            </div>
        </li>
    );
}

export default function Members({
    workspace,
    members,
    canManageMembers,
}: MemberPageProps) {
    const form = useForm({ email: '', role: 'member' as WorkspaceRole });

    const addMember = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(
            route('workspaces.members.store', { workspace: workspace.id }),
            { preserveScroll: true, onSuccess: () => form.reset('email') },
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Members · ${workspace.name}`} />

            <main className="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <div>
                    <Link
                        href={route('workspaces.show', {
                            workspace: workspace.id,
                        })}
                        className="text-sm font-semibold text-[#557c70] transition hover:text-ink"
                    >
                        ← {workspace.name}
                    </Link>
                    <h1 className="mt-3 text-2xl font-semibold tracking-tight text-ink">
                        Workspace members
                    </h1>
                    <p className="mt-1 text-sm text-ink-muted">
                        Manage who can collaborate on this workspace.
                    </p>
                </div>

                {canManageMembers && (
                    <form
                        onSubmit={addMember}
                        className="grid gap-3 rounded-2xl border border-border bg-white p-5 shadow-[0_2px_12px_rgba(31,36,33,0.04)] sm:grid-cols-[minmax(0,1fr)_140px_auto]"
                    >
                        <div>
                            <label
                                htmlFor="member-email"
                                className="sr-only"
                            >
                                Registered member email
                            </label>
                            <input
                                id="member-email"
                                type="email"
                                value={form.data.email}
                                onChange={(event) =>
                                    form.setData('email', event.target.value)
                                }
                                placeholder="Add a registered user by email"
                                required
                                className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-sage-hover focus:ring-sage/40"
                            />
                            {form.errors.email && (
                                <p role="alert" className="mt-1 text-xs text-red-600">
                                    {form.errors.email}
                                </p>
                            )}
                        </div>
                        <div>
                            <label htmlFor="member-role" className="sr-only">
                                New member role
                            </label>
                            <select
                                id="member-role"
                                value={form.data.role}
                                onChange={(event) =>
                                    form.setData(
                                        'role',
                                        event.target.value as WorkspaceRole,
                                    )
                                }
                                className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-sage-hover focus:ring-sage/40"
                            >
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                            {form.errors.role && (
                                <p role="alert" className="mt-1 text-xs text-red-600">
                                    {form.errors.role}
                                </p>
                            )}
                        </div>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Add member
                        </button>
                    </form>
                )}

                <section className="overflow-hidden rounded-2xl border border-border bg-white shadow-[0_2px_12px_rgba(31,36,33,0.04)]">
                    <ul className="divide-y divide-border">
                        {members.map((member) => (
                            <MemberRow
                                key={member.id}
                                workspaceId={workspace.id}
                                member={member}
                                canManageMembers={canManageMembers}
                            />
                        ))}
                    </ul>
                </section>
            </main>
        </AuthenticatedLayout>
    );
}
