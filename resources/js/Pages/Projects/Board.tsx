import ActivityFeed from '@/Components/ActivityFeed';
import BoardColumn from '@/Components/BoardColumn';
import TaskDetailDrawer from '@/Components/TaskDetailDrawer';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useBoardStore } from '@/stores/useBoardStore';
import {
    BoardColumn as BoardColumnType,
    BoardTask,
    ProjectActivity,
    ProjectBoard,
    TaskPriority,
    User,
} from '@/types/delox';
import {
    closestCorners,
    DndContext,
    DragEndEvent,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    sortableKeyboardCoordinates,
    horizontalListSortingStrategy,
} from '@dnd-kit/sortable';
import { Head, router, useForm, usePoll } from '@inertiajs/react';
import axios from 'axios';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import { History, Pencil, Trash2, X } from 'lucide-react';

interface BoardPageProps {
    project: ProjectBoard;
    columns: BoardColumnType[];
    activities: ProjectActivity[];
    members: User[];
    canManageProjects: boolean;
}

interface TaskEvent {
    task: BoardTask;
}

interface TaskDeletedEvent {
    taskId: number;
}

interface ColumnEvent {
    column: Omit<BoardColumnType, 'tasks'>;
}

interface ColumnDeletedEvent {
    columnId: number;
}

interface CommentEvent {
    comment: BoardTask['comments'][number];
}

const priorityFilterStyles: Record<TaskPriority, string> = {
    low: 'bg-[#9dbeb5]/25 text-[#25443c]',
    medium: 'bg-[#efefef] text-[#373d3a]',
    high: 'bg-[#d8b9a9]/35 text-[#5a3626]',
    urgent: 'bg-[#c96a50]/15 text-[#8f3822]',
};

export default function Board({
    project,
    columns,
    activities,
    members,
    canManageProjects,
}: BoardPageProps) {
    const boardColumns = useBoardStore((state) => state.columns);
    const activeTaskId = useBoardStore((state) => state.activeTaskId);
    const error = useBoardStore((state) => state.error);
    const setColumns = useBoardStore((state) => state.setColumns);
    const setActiveTaskId = useBoardStore((state) => state.setActiveTaskId);
    const clearError = useBoardStore((state) => state.clearError);
    const updateTask = useBoardStore((state) => state.updateTask);
    const removeTask = useBoardStore((state) => state.removeTask);
    const addColumn = useBoardStore((state) => state.addColumn);
    const updateColumn = useBoardStore((state) => state.updateColumn);
    const removeColumn = useBoardStore((state) => state.removeColumn);
    const addComment = useBoardStore((state) => state.addComment);
    const moveTask = useBoardStore((state) => state.moveTask);
    const moveColumn = useBoardStore((state) => state.moveColumn);
    const setError = useBoardStore((state) => state.setError);

    const [search, setSearch] = useState('');
    const [priorityFilter, setPriorityFilter] = useState<TaskPriority | 'all'>(
        'all',
    );
    const [assigneeFilter, setAssigneeFilter] = useState('all');
    const [newColumnName, setNewColumnName] = useState('');
    const [creatingColumn, setCreatingColumn] = useState(false);
    const [editingProject, setEditingProject] = useState(false);
    const [activityOpen, setActivityOpen] = useState(false);
    const { start: startActivityPolling, stop: stopActivityPolling } = usePoll(
        30_000,
        { only: ['activities'] },
        { autoStart: false },
    );
    const requestedTaskId = new URLSearchParams(window.location.search).get(
        'task_id',
    );
    const projectForm = useForm({
        name: project.name,
        key: project.key,
        description: project.description ?? '',
    });

    useEffect(() => {
        setColumns(columns);
    }, [columns, setColumns]);

    useEffect(() => {
        if (requestedTaskId === null) {
            return;
        }

        const taskId = Number(requestedTaskId);
        const taskExists = columns.some((column) =>
            column.tasks.some((task) => task.id === taskId),
        );

        if (Number.isSafeInteger(taskId) && taskExists) {
            setActiveTaskId(taskId);
        }
    }, [columns, requestedTaskId, setActiveTaskId]);

    useEffect(() => {
        if (activityOpen) {
            startActivityPolling();
        } else {
            stopActivityPolling();
        }

        return stopActivityPolling;
    }, [activityOpen, startActivityPolling, stopActivityPolling]);

    useEffect(() => {
        const echo = window.Echo;

        if (!echo) {
            return;
        }

        const channel = echo.private(`project.${project.id}`);

        channel
            .listen('.TaskCreated', (event: TaskEvent) => updateTask(event.task))
            .listen('.TaskUpdated', (event: TaskEvent) => updateTask(event.task))
            .listen('.TaskMoved', (event: TaskEvent) => updateTask(event.task))
            .listen('.TaskDeleted', (event: TaskDeletedEvent) =>
                removeTask(event.taskId),
            )
            .listen('.ColumnCreated', (event: ColumnEvent) =>
                addColumn({ ...event.column, tasks: [] }),
            )
            .listen('.ColumnMoved', (event: ColumnEvent) => {
                const currentColumn = useBoardStore
                    .getState()
                    .columns.find((column) => column.id === event.column.id);

                if (currentColumn) {
                    updateColumn({
                        ...currentColumn,
                        position: event.column.position,
                    });
                }
            })
            .listen('.ColumnDeleted', (event: ColumnDeletedEvent) =>
                removeColumn(event.columnId),
            )
            .listen('.TaskCommentCreated', (event: CommentEvent) =>
                addComment(event.comment),
            );

        return () => {
            echo.leave(`project.${project.id}`);
        };
    }, [
        addColumn,
        addComment,
        project.id,
        removeColumn,
        removeTask,
        updateColumn,
        updateTask,
    ]);

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const allTasks = useMemo(
        () => boardColumns.flatMap((column) => column.tasks),
        [boardColumns],
    );
    const selectedTask =
        allTasks.find((task) => task.id === activeTaskId) ?? null;
    const selectedColumnName =
        boardColumns.find((column) => column.id === selectedTask?.column_id)
            ?.name ?? 'Unknown';

    const filteredColumns = useMemo(() => {
        const normalizedSearch = search.trim().toLocaleLowerCase();

        return boardColumns.map((column) => ({
            ...column,
            tasks: column.tasks.filter((task) => {
                const matchesSearch =
                    normalizedSearch.length === 0 ||
                    task.title.toLocaleLowerCase().includes(normalizedSearch) ||
                    `${project.key}-${task.number}`
                        .toLocaleLowerCase()
                        .includes(normalizedSearch);
                const matchesPriority =
                    priorityFilter === 'all' || task.priority === priorityFilter;
                const matchesAssignee =
                    assigneeFilter === 'all' ||
                    (assigneeFilter === 'unassigned'
                        ? task.assignee_id === null
                        : task.assignee_id?.toString() === assigneeFilter);

                return matchesSearch && matchesPriority && matchesAssignee;
            }),
        }));
    }, [assigneeFilter, boardColumns, priorityFilter, project.key, search]);

    const createColumn = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const name = newColumnName.trim();

        if (!canManageProjects || !name || creatingColumn) {
            return;
        }

        setCreatingColumn(true);
        clearError();

        try {
            const response = await window.axios.post<{
                column: Omit<BoardColumnType, 'tasks'>;
            }>(route('columns.store', { project: project.id }), { name });

            addColumn({ ...response.data.column, tasks: [] });
            setNewColumnName('');
        } catch (requestError: unknown) {
            setError(
                axios.isAxiosError<{ message?: string }>(requestError)
                    ? requestError.response?.data?.message ?? 'Could not create the column.'
                    : 'Could not create the column.',
            );
        } finally {
            setCreatingColumn(false);
        }
    };

    const finishDrag = ({ active, over }: DragEndEvent) => {
        if (!canManageProjects || !over || active.id === over.id) {
            return;
        }

        const activeData = active.data.current;
        const overData = over.data.current;

        if (!activeData || !overData) {
            return;
        }

        if (activeData.type === 'column') {
            const columnId = activeData.columnId;
            const destinationColumnId = overData.columnId;

            if (
                typeof columnId !== 'number' ||
                typeof destinationColumnId !== 'number'
            ) {
                return;
            }

            const sourceIndex = boardColumns.findIndex(
                (column) => column.id === columnId,
            );
            const overIndex = boardColumns.findIndex(
                (column) => column.id === destinationColumnId,
            );
            const withoutActive = boardColumns.filter(
                (column) => column.id !== columnId,
            );
            let destinationIndex = withoutActive.findIndex(
                (column) => column.id === destinationColumnId,
            );

            if (sourceIndex < overIndex) {
                destinationIndex += 1;
            }

            void moveColumn(columnId, destinationIndex);

            return;
        }

        if (activeData.type !== 'task') {
            return;
        }

        const taskId = activeData.taskId;
        const destinationColumnId = overData.columnId;

        if (
            typeof taskId !== 'number' ||
            typeof destinationColumnId !== 'number'
        ) {
            return;
        }

        const sourceColumn = boardColumns.find((column) =>
            column.tasks.some((task) => task.id === taskId),
        );
        const destinationColumn = boardColumns.find(
            (column) => column.id === destinationColumnId,
        );

        if (!sourceColumn || !destinationColumn) {
            return;
        }

        const destinationTasks = destinationColumn.tasks.filter(
            (task) => task.id !== taskId,
        );
        let destinationIndex =
            overData.type === 'task'
                ? destinationTasks.findIndex((task) => task.id === overData.taskId)
                : destinationTasks.length;

        if (destinationIndex < 0) {
            destinationIndex = destinationTasks.length;
        }

        if (
            sourceColumn.id === destinationColumn.id &&
            sourceColumn.tasks.findIndex((task) => task.id === taskId) <
            destinationColumn.tasks.findIndex(
                (task) => task.id === overData.taskId,
            )
        ) {
            destinationIndex += 1;
        }

        void moveTask(taskId, destinationColumnId, destinationIndex);
    };

    const saveProject = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        projectForm.transform((data) => ({
            ...data,
            key: data.key.trim().toUpperCase(),
        }));
        projectForm.patch(route('projects.update', { project: project.id }), {
            onSuccess: () => setEditingProject(false),
        });
    };

    const deleteProject = () => {
        if (
            !window.confirm(
                `Delete "${project.name}" and all of its tasks? This cannot be undone.`,
            )
        ) {
            return;
        }

        router.delete(route('projects.destroy', { project: project.id }));
    };

    const openActivityFeed = () => {
        setActivityOpen(true);
        router.reload({ only: ['activities'] });
    };

    return (
        <AuthenticatedLayout>
            <Head title={`${project.key} · ${project.name}`} />

            <main className="flex min-h-[calc(100vh-4rem)] flex-col bg-canvas">
                <header className="flex flex-wrap items-center justify-between gap-4 border-b border-border bg-white/80 px-4 py-4 backdrop-blur-sm sm:px-6">
                    <div className="flex items-center gap-3">
                        <span className="rounded-lg bg-sand/30 px-2 py-1 font-mono text-xs font-semibold tabular-nums text-[#5a3626]">
                            {project.key}
                        </span>
                        <h1 className="text-lg font-semibold tracking-tight text-ink">
                            {project.name}
                        </h1>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        {canManageProjects && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setEditingProject(true)}
                                    className="inline-flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2 text-sm font-semibold text-ink-body transition hover:border-sage hover:bg-sage/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/50"
                                >
                                    <Pencil size={15} aria-hidden="true" />
                                    Edit project
                                </button>
                                <button
                                    type="button"
                                    onClick={deleteProject}
                                    className="inline-flex items-center gap-2 rounded-xl border border-[#c96a50]/30 px-3 py-2 text-sm font-semibold text-[#9f4633] transition hover:bg-[#c96a50]/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#c96a50]/30"
                                >
                                    <Trash2 size={15} aria-hidden="true" />
                                    Delete project
                                </button>
                            </>
                        )}
                        <label htmlFor="task-search" className="sr-only">
                            Search tasks
                        </label>
                        <input
                            id="task-search"
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search tasks..."
                            className="w-44 rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                        />
                        <div
                            id="priority-filter"
                            role="group"
                            aria-label="Filter by priority"
                            className="flex items-center gap-1 rounded-xl border border-border bg-white p-1"
                        >
                            {(
                                [
                                    'all',
                                    'urgent',
                                    'high',
                                    'medium',
                                    'low',
                                ] as const
                            ).map((priority) => {
                                const isActive = priorityFilter === priority;

                                return (
                                    <button
                                        key={priority}
                                        type="button"
                                        aria-pressed={isActive}
                                        onClick={() =>
                                            setPriorityFilter(priority)
                                        }
                                        className={`rounded-lg px-2.5 py-1.5 text-xs font-semibold capitalize transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage ${isActive
                                                ? priority === 'all'
                                                    ? 'bg-sage/30 text-ink'
                                                    : priorityFilterStyles[priority]
                                                : 'text-ink-muted hover:bg-surface-subtle hover:text-ink-body'
                                            }`}
                                    >
                                        {priority === 'all'
                                            ? 'All'
                                            : priority}
                                    </button>
                                );
                            })}
                        </div>
                        <label htmlFor="assignee-filter" className="sr-only">
                            Filter by assignee
                        </label>
                        <select
                            id="assignee-filter"
                            value={assigneeFilter}
                            onChange={(event) =>
                                setAssigneeFilter(event.target.value)
                            }
                            className="max-w-40 rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                        >
                            <option value="all">All assignees</option>
                            {members.map((member) => (
                                <option key={member.id} value={member.id}>
                                    {member.name}
                                </option>
                            ))}
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>
                </header>

                {error && (
                    <div
                        role="alert"
                        className="flex items-center justify-between gap-3 border-b border-[#c96a50]/30 bg-[#c96a50]/10 px-6 py-3 text-sm text-[#8f3822]"
                    >
                        {error}
                        <button
                            type="button"
                            onClick={clearError}
                            className="font-medium underline"
                        >
                            Dismiss
                        </button>
                    </div>
                )}

                <div className="flex flex-1 flex-col gap-3 overflow-hidden p-4 sm:p-6">
                    <div className="flex items-center justify-between gap-4">
                        <p className="text-xs text-ink-muted">
                            Keep work moving, together.
                        </p>
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={openActivityFeed}
                                className="inline-flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2.5 text-sm font-semibold text-ink-body transition hover:border-sage hover:bg-sage/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/50"
                            >
                                <History size={15} aria-hidden="true" />
                                Activity
                            </button>
                            {canManageProjects && (
                                <form
                                    onSubmit={createColumn}
                                    className="flex items-center gap-2"
                                >
                                <label
                                    htmlFor="new-column-name"
                                    className="sr-only"
                                >
                                    New column name
                                </label>
                                <input
                                    id="new-column-name"
                                    value={newColumnName}
                                    onChange={(event) =>
                                        setNewColumnName(event.target.value)
                                    }
                                    placeholder="New column"
                                    maxLength={100}
                                    className="w-36 rounded-xl border border-[#dcd9d3] bg-white text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                />
                                <button
                                    type="submit"
                                    disabled={
                                        creatingColumn || !newColumnName.trim()
                                    }
                                    className="inline-flex items-center gap-2 rounded-xl bg-sage px-3.5 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <span aria-hidden="true">+</span>
                                    Add Column
                                </button>
                                </form>
                            )}
                        </div>
                    </div>

                    <DndContext
                        sensors={canManageProjects ? sensors : []}
                        collisionDetection={closestCorners}
                        onDragEnd={finishDrag}
                    >
                        <SortableContext
                            items={boardColumns.map(
                                (column) => `column-${column.id}`,
                            )}
                            strategy={horizontalListSortingStrategy}
                        >
                            <div className="flex min-h-0 flex-1 items-start gap-4 overflow-x-auto pb-4">
                                {filteredColumns.map((column) => (
                                    <BoardColumn
                                        key={column.id}
                                        column={column}
                                        projectId={project.id}
                                        projectKey={project.key}
                                        canManage={canManageProjects}
                                        onOpenTask={(task) =>
                                            setActiveTaskId(task.id)
                                        }
                                    />
                                ))}
                                {boardColumns.length === 0 && (
                                    <p className="rounded-2xl border border-dashed border-sage/60 bg-white/60 p-6 text-sm text-ink-muted">
                                        This project has no columns yet.
                                    </p>
                                )}
                            </div>
                        </SortableContext>
                    </DndContext>
                </div>
            </main>

            {activityOpen && (
                <ActivityFeed
                    activities={activities}
                    onClose={() => setActivityOpen(false)}
                    onSelectTask={(taskId) => {
                        setActivityOpen(false);
                        setActiveTaskId(taskId);
                    }}
                />
            )}

            <TaskDetailDrawer
                task={selectedTask}
                members={members}
                projectKey={project.key}
                columnName={selectedColumnName}
                canManage={canManageProjects}
                onClose={() => setActiveTaskId(null)}
                onTaskUpdated={updateTask}
                onTaskDeleted={removeTask}
                onCommentAdded={addComment}
            />

            {editingProject && (
                <div
                    className="fixed inset-0 z-[60] flex items-center justify-center bg-ink/35 p-4 backdrop-blur-[2px]"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) {
                            setEditingProject(false);
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="edit-project-title"
                        className="w-full max-w-lg rounded-2xl border border-border bg-white p-6 shadow-2xl"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2
                                    id="edit-project-title"
                                    className="text-lg font-semibold text-ink"
                                >
                                    Edit project
                                </h2>
                                <p className="mt-1 text-sm text-ink-muted">
                                    Update the name, key, or description.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setEditingProject(false)}
                                className="rounded-lg p-2 text-ink-muted transition hover:bg-surface-subtle hover:text-ink"
                                aria-label="Close project editor"
                            >
                                <X size={18} aria-hidden="true" />
                            </button>
                        </header>
                        <form onSubmit={saveProject} className="space-y-4">
                            <label className="block text-sm font-semibold text-ink-body">
                                Name
                                <input
                                    value={projectForm.data.name}
                                    onChange={(event) =>
                                        projectForm.setData('name', event.target.value)
                                    }
                                    maxLength={255}
                                    required
                                    className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                />
                                {projectForm.errors.name && (
                                    <span role="alert" className="mt-1 block text-xs text-[#9f4633]">
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
                                            event.target.value.toUpperCase().slice(0, 6),
                                        )
                                    }
                                    maxLength={6}
                                    required
                                    className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] font-mono uppercase tabular-nums text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                />
                                {projectForm.errors.key && (
                                    <span role="alert" className="mt-1 block text-xs text-[#9f4633]">
                                        {projectForm.errors.key}
                                    </span>
                                )}
                            </label>
                            <label className="block text-sm font-semibold text-ink-body">
                                Description
                                <textarea
                                    value={projectForm.data.description}
                                    onChange={(event) =>
                                        projectForm.setData('description', event.target.value)
                                    }
                                    rows={3}
                                    maxLength={10000}
                                    className="mt-1.5 w-full rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de]"
                                />
                                {projectForm.errors.description && (
                                    <span role="alert" className="mt-1 block text-xs text-[#9f4633]">
                                        {projectForm.errors.description}
                                    </span>
                                )}
                            </label>
                            <footer className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setEditingProject(false)}
                                    className="rounded-xl border border-border px-4 py-2.5 text-sm font-semibold text-ink-body transition hover:bg-surface-subtle"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={projectForm.processing}
                                    className="rounded-xl bg-sage px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {projectForm.processing ? 'Saving...' : 'Save changes'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
