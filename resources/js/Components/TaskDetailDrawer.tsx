import { BoardTask, TaskComment, TaskPriority, User } from '@/types/delox';
import axios from 'axios';
import { FormEvent, useEffect, useState } from 'react';

interface TaskDetailDrawerProps {
    task: BoardTask | null;
    members: User[];
    projectKey: string;
    columnName: string;
    onClose: () => void;
    onTaskUpdated: (task: BoardTask) => void;
    onTaskDeleted: (taskId: number) => void;
    onCommentAdded: (comment: TaskComment) => void;
}

export default function TaskDetailDrawer({
    task,
    members,
    projectKey,
    columnName,
    onClose,
    onTaskUpdated,
    onTaskDeleted,
    onCommentAdded,
}: TaskDetailDrawerProps) {
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [priority, setPriority] = useState<TaskPriority>('medium');
    const [assigneeId, setAssigneeId] = useState('');
    const [dueDate, setDueDate] = useState('');
    const [commentBody, setCommentBody] = useState('');
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!task) {
            return;
        }

        setTitle(task.title);
        setDescription(task.description ?? '');
        setPriority(task.priority);
        setAssigneeId(task.assignee_id?.toString() ?? '');
        setDueDate(task.due_date ?? '');
        setCommentBody('');
        setError(null);
    }, [task?.id]);

    useEffect(() => {
        if (!task) {
            return;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [task, onClose]);

    if (!task) {
        return null;
    }

    const saveTask = async () => {
        setSaving(true);
        setError(null);

        try {
            const response = await window.axios.patch<{ task: BoardTask }>(
                route('tasks.update', { task: task.id }),
                {
                    title,
                    description: description.trim() || null,
                    priority,
                    assignee_id: assigneeId ? Number(assigneeId) : null,
                    due_date: dueDate || null,
                },
            );

            onTaskUpdated(response.data.task);
        } catch (requestError: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(requestError)
                    ? requestError.response?.data?.message ?? 'Could not save this task.'
                    : 'Could not save this task.',
            );
        } finally {
            setSaving(false);
        }
    };

    const addComment = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const body = commentBody.trim();

        if (!body) {
            return;
        }

        setSaving(true);
        setError(null);

        try {
            const response = await window.axios.post<{ comment: TaskComment }>(
                route('tasks.comments.store', { task: task.id }),
                { body },
            );

            onCommentAdded(response.data.comment);
            setCommentBody('');
        } catch (requestError: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(requestError)
                    ? requestError.response?.data?.message ?? 'Could not post this comment.'
                    : 'Could not post this comment.',
            );
        } finally {
            setSaving(false);
        }
    };

    const deleteTask = async () => {
        if (!window.confirm(`Delete ${projectKey}-${task.number}?`)) {
            return;
        }

        setSaving(true);
        setError(null);

        try {
            await window.axios.delete(route('tasks.destroy', { task: task.id }));
            onTaskDeleted(task.id);
            onClose();
        } catch (requestError: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(requestError)
                    ? requestError.response?.data?.message ?? 'Could not delete this task.'
                    : 'Could not delete this task.',
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex justify-end" role="presentation">
            <button
                type="button"
                aria-label="Close task details"
                className="absolute inset-0 cursor-default bg-ink/35 backdrop-blur-[2px]"
                onClick={onClose}
            />
            <aside
                role="dialog"
                aria-modal="true"
                aria-labelledby="task-drawer-title"
                className="relative flex h-full w-full max-w-xl animate-[drawer-in_180ms_ease-out] flex-col overflow-y-auto border-l border-border bg-white shadow-2xl"
            >
                <header className="sticky top-0 z-10 flex items-center justify-between border-b border-border bg-white/95 px-6 py-4 backdrop-blur">
                    <span className="font-mono text-xs font-medium tabular-nums text-ink-muted">
                        {projectKey}-{task.number}
                    </span>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-xl px-3 py-1.5 text-sm font-medium text-ink-muted transition hover:bg-sand/15 hover:text-ink"
                    >
                        Close
                    </button>
                </header>

                <div className="flex flex-1 flex-col">
                    <div className="flex-1 space-y-6 px-6 py-6">
                        <label className="block">
                            <span className="sr-only">Task title</span>
                            <input
                                id="task-drawer-title"
                                value={title}
                                onChange={(event) => setTitle(event.target.value)}
                                maxLength={255}
                                required
                                className="w-full border-0 bg-transparent p-0 text-2xl font-semibold tracking-tight text-ink placeholder:text-ink-muted/60 focus:ring-0"
                            />
                        </label>

                        <label className="block">
                            <span className="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-ink-muted">
                                Description
                            </span>
                            <textarea
                                value={description}
                                onChange={(event) => setDescription(event.target.value)}
                                rows={5}
                                maxLength={20000}
                                placeholder="Add details or notes..."
                                className="w-full resize-y rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm leading-6 text-ink-body focus:border-sage-hover focus:ring-sage/40"
                            />
                            {description && (
                                <p className="mt-2 whitespace-pre-wrap rounded-xl bg-surface-subtle p-3 text-sm leading-6 text-ink-body">
                                    {description}
                                </p>
                            )}
                        </label>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="rounded-xl border border-border bg-surface-subtle p-3">
                                <span className="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-ink-muted">
                                    Status / column
                                </span>
                                <p className="truncate text-sm font-semibold text-ink">
                                    {columnName}
                                </p>
                            </div>
                            <label className="block">
                                <span className="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-ink-muted">
                                    Priority
                                </span>
                                <select
                                    value={priority}
                                    onChange={(event) =>
                                        setPriority(event.target.value as TaskPriority)
                                    }
                                    className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm capitalize text-ink-body focus:border-sage-hover focus:ring-sage/40"
                                >
                                    {(['low', 'medium', 'high', 'urgent'] as const).map(
                                        (option) => (
                                            <option key={option} value={option}>
                                                {option}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </label>
                            <label className="block">
                                <span className="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-ink-muted">
                                    Assignee
                                </span>
                                <select
                                    value={assigneeId}
                                    onChange={(event) => setAssigneeId(event.target.value)}
                                    className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-sage-hover focus:ring-sage/40"
                                >
                                    <option value="">Unassigned</option>
                                    {members.map((member) => (
                                        <option key={member.id} value={member.id}>
                                            {member.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-ink-muted">
                                    Due date
                                </span>
                                <input
                                    type="date"
                                    value={dueDate}
                                    onChange={(event) => setDueDate(event.target.value)}
                                    className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm tabular-nums text-ink-body focus:border-sage-hover focus:ring-sage/40"
                                />
                            </label>
                        </div>

                        <section aria-labelledby="activity-heading" className="space-y-4">
                            <h2
                                id="activity-heading"
                                className="text-sm font-semibold text-ink"
                            >
                                Activity
                            </h2>
                            {task.comments.map((comment) => (
                                <article
                                    key={comment.id}
                                    className="rounded-xl border border-border bg-white p-3.5"
                                >
                                    <div className="flex items-center justify-between gap-3 text-xs">
                                        <span className="font-semibold text-ink-body">
                                            {comment.user.name}
                                        </span>
                                        <time className="tabular-nums text-ink-muted">
                                            {new Date(comment.created_at).toLocaleString()}
                                        </time>
                                    </div>
                                    <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-ink-body">
                                        {comment.body}
                                    </p>
                                </article>
                            ))}
                            {task.comments.length === 0 && (
                                <p className="rounded-xl border border-dashed border-border px-4 py-5 text-center text-sm text-ink-muted">
                                    No comments yet. Start the conversation.
                                </p>
                            )}
                            <form onSubmit={addComment} className="space-y-2">
                                <label htmlFor="task-comment" className="sr-only">
                                    Add a comment
                                </label>
                                <textarea
                                    id="task-comment"
                                    value={commentBody}
                                    onChange={(event) => setCommentBody(event.target.value)}
                                    rows={3}
                                    maxLength={10000}
                                    placeholder="Write a comment..."
                                    className="w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-sage-hover focus:ring-sage/40"
                                />
                                <div className="flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={saving || !commentBody.trim()}
                                        className="rounded-xl bg-sage px-3.5 py-2 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        Post comment
                                    </button>
                                </div>
                            </form>
                        </section>

                        {error && (
                            <p role="alert" className="rounded-xl bg-[#c96a50]/10 px-3 py-2 text-sm text-[#8f3822]">
                                {error}
                            </p>
                        )}
                    </div>

                    <footer className="sticky bottom-0 flex items-center justify-between border-t border-border bg-white/95 px-6 py-4 backdrop-blur">
                        <button
                            type="button"
                            disabled={saving}
                            onClick={deleteTask}
                            className="rounded-xl px-3 py-2 text-sm font-semibold text-[#9f4633] transition hover:bg-[#c96a50]/10 disabled:opacity-40"
                        >
                            Delete task
                        </button>
                        <button
                            type="button"
                            onClick={() => void saveTask()}
                            disabled={saving || !title.trim()}
                            className="rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {saving ? 'Saving...' : 'Save changes'}
                        </button>
                    </footer>
                </div>
            </aside>
        </div>
    );
}
