import TaskCard from '@/Components/TaskCard';
import { useBoardStore } from '@/stores/useBoardStore';
import { BoardColumn as BoardColumnType, BoardTask } from '@/types/delox';
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import axios from 'axios';
import { GripVertical, Plus, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';

interface BoardColumnProps {
    column: BoardColumnType;
    projectId: number;
    projectKey: string;
    onOpenTask: (task: BoardTask) => void;
}

export default function BoardColumn({
    column,
    projectId,
    projectKey,
    onOpenTask,
}: BoardColumnProps) {
    const addTask = useBoardStore((state) => state.addTask);
    const removeColumn = useBoardStore((state) => state.removeColumn);
    const setError = useBoardStore((state) => state.setError);
    const [title, setTitle] = useState('');
    const [creating, setCreating] = useState(false);

    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
        isOver,
    } = useSortable({
        id: `column-${column.id}`,
        data: { type: 'column', columnId: column.id },
    });

    const createTask = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const taskTitle = title.trim();

        if (!taskTitle || creating) {
            return;
        }

        setCreating(true);

        try {
            const response = await window.axios.post<{ task: BoardTask }>(
                route('tasks.store', { project: projectId }),
                {
                    column_id: column.id,
                    title: taskTitle,
                    priority: 'medium',
                },
            );
            addTask(response.data.task);
            setTitle('');
        } catch (error: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(error)
                    ? error.response?.data?.message ?? 'Could not create the task.'
                    : 'Could not create the task.',
            );
        } finally {
            setCreating(false);
        }
    };

    const deleteColumn = async () => {
        if (!window.confirm(`Delete "${column.name}" and all tasks in it?`)) {
            return;
        }

        try {
            await window.axios.delete(
                route('columns.destroy', { column: column.id }),
            );
            removeColumn(column.id);
        } catch (error: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(error)
                    ? error.response?.data?.message ?? 'Could not delete the column.'
                    : 'Could not delete the column.',
            );
        }
    };

    return (
        <section
            ref={setNodeRef}
            style={{
                transform: CSS.Transform.toString(transform),
                transition,
            }}
            className={`flex h-full w-80 shrink-0 flex-col rounded-2xl border border-border-subtle bg-surface-subtle transition-colors ${
                isDragging
                    ? 'rotate-[0.5deg] opacity-60'
                    : isOver
                      ? 'border-sage ring-2 ring-sage/30'
                      : ''
            }`}
            aria-label={`${column.name} column`}
        >
            <header className="flex items-center justify-between gap-2 border-b border-border-subtle px-3.5 py-3.5">
                <div className="flex min-w-0 items-center gap-2">
                    <button
                        type="button"
                        className="cursor-grab rounded-lg p-1 text-ink-muted/60 transition hover:bg-sand/20 hover:text-ink-body active:cursor-grabbing"
                        aria-label={`Reorder ${column.name}`}
                        {...attributes}
                        {...listeners}
                    >
                        <GripVertical size={16} aria-hidden="true" />
                    </button>
                    <h2 className="truncate text-sm font-semibold text-ink">
                        {column.name}
                    </h2>
                    <span className="rounded-full bg-canvas px-2 py-0.5 text-xs font-medium tabular-nums text-ink-muted">
                        {column.tasks.length}
                    </span>
                </div>
                <button
                    type="button"
                    onClick={deleteColumn}
                    className="rounded-lg p-1.5 text-ink-muted/60 transition hover:bg-[#c96a50]/10 hover:text-[#9f4633]"
                    aria-label={`Delete ${column.name} column`}
                >
                    <Trash2 size={14} aria-hidden="true" />
                </button>
            </header>

            <SortableContext
                items={column.tasks.map((task) => `task-${task.id}`)}
                strategy={verticalListSortingStrategy}
            >
                <div className="flex min-h-24 flex-1 flex-col gap-2 overflow-y-auto p-2.5">
                    {column.tasks.map((task) => (
                        <TaskCard
                            key={task.id}
                            task={task}
                            projectKey={projectKey}
                            onOpen={onOpenTask}
                        />
                    ))}
                </div>
            </SortableContext>

            <form
                onSubmit={createTask}
                className="flex items-center gap-2 border-t border-border-subtle p-2.5"
            >
                <input
                    aria-label={`New task in ${column.name}`}
                    value={title}
                    onChange={(event) => setTitle(event.target.value)}
                    placeholder="Add a task..."
                    maxLength={255}
                    className="h-10 min-w-0 flex-1 rounded-xl border-0 bg-transparent px-3 py-2.5 text-sm text-ink-body placeholder:text-ink-muted/70 focus:ring-2 focus:ring-sage/50"
                />
                <button
                    type="submit"
                    disabled={creating || title.trim().length === 0}
                    className="flex size-10 items-center justify-center rounded-xl text-ink-muted transition hover:bg-white hover:text-ink disabled:opacity-40"
                    aria-label="Create task"
                >
                    <Plus size={16} aria-hidden="true" />
                </button>
            </form>
        </section>
    );
}
