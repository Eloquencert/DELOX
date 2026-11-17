import BoardColumn from '@/Components/BoardColumn';
import TaskDetailDrawer from '@/Components/TaskDetailDrawer';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useBoardStore } from '@/stores/useBoardStore';
import {
    BoardColumn as BoardColumnType,
    BoardTask,
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
import { Head, Link } from '@inertiajs/react';
import axios from 'axios';
import { FormEvent, useEffect, useMemo, useState } from 'react';

interface BoardPageProps {
    project: ProjectBoard;
    columns: BoardColumnType[];
    members: User[];
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

export default function Board({ project, columns, members }: BoardPageProps) {
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

    useEffect(() => {
        setColumns(columns);
    }, [columns, setColumns]);

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

        if (!name || creatingColumn) {
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
        if (!over || active.id === over.id) {
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
                        <label htmlFor="task-search" className="sr-only">
                            Search tasks
                        </label>
                        <input
                            id="task-search"
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search tasks..."
                            className="w-44 rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-sage-hover focus:ring-sage/40"
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
                                        className={`rounded-lg px-2.5 py-1.5 text-xs font-semibold capitalize transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage ${
                                            isActive
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
                            className="max-w-40 rounded-xl border border-[#dcd9d3] bg-[#faf9f7] text-sm text-ink-body focus:border-sage-hover focus:ring-sage/40"
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
                        <form
                            onSubmit={createColumn}
                            className="flex items-center gap-2"
                        >
                            <label htmlFor="new-column-name" className="sr-only">
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
                                className="w-36 rounded-xl border border-[#dcd9d3] bg-white text-sm text-ink-body placeholder:text-ink-muted/70 focus:border-sage-hover focus:ring-sage/40"
                            />
                            <button
                                type="submit"
                                disabled={creatingColumn || !newColumnName.trim()}
                                className="inline-flex items-center gap-2 rounded-xl bg-sage px-3.5 py-2.5 text-sm font-semibold text-ink transition hover:bg-sage-hover disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <span aria-hidden="true">+</span>
                                Add Column
                            </button>
                        </form>
                    </div>

                    <DndContext
                        sensors={sensors}
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

            <TaskDetailDrawer
                task={selectedTask}
                members={members}
                projectKey={project.key}
                columnName={selectedColumnName}
                onClose={() => setActiveTaskId(null)}
                onTaskUpdated={updateTask}
                onTaskDeleted={removeTask}
                onCommentAdded={addComment}
            />
        </AuthenticatedLayout>
    );
}
