import { BoardColumn, BoardTask, TaskComment } from '@/types/delox';
import axios from 'axios';
import { create } from 'zustand';

interface BoardState {
    columns: BoardColumn[];
    activeTaskId: number | null;
    error: string | null;
    setColumns: (columns: BoardColumn[]) => void;
    setActiveTaskId: (taskId: number | null) => void;
    clearError: () => void;
    setError: (error: string) => void;
    addTask: (task: BoardTask) => void;
    updateTask: (task: BoardTask) => void;
    removeTask: (taskId: number) => void;
    addColumn: (column: Omit<BoardColumn, 'tasks'> & { tasks?: BoardTask[] }) => void;
    updateColumn: (column: BoardColumn) => void;
    removeColumn: (columnId: number) => void;
    addComment: (comment: TaskComment) => void;
    moveTask: (taskId: number, columnId: number, index: number) => Promise<void>;
    moveColumn: (columnId: number, index: number) => Promise<void>;
}

function errorMessage(error: unknown): string {
    if (axios.isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? 'The board update could not be saved.';
    }

    return 'The board update could not be saved.';
}

function sortTasks(tasks: BoardTask[]): BoardTask[] {
    return [...tasks].sort((first, second) =>
        first.position.localeCompare(second.position),
    );
}

function sortColumns(columns: BoardColumn[]): BoardColumn[] {
    return [...columns].sort((first, second) =>
        first.position.localeCompare(second.position),
    );
}

export const useBoardStore = create<BoardState>((set, get) => ({
    columns: [],
    activeTaskId: null,
    error: null,

    setColumns: (columns) => set({ columns: sortColumns(columns), error: null }),
    setActiveTaskId: (activeTaskId) => set({ activeTaskId }),
    clearError: () => set({ error: null }),
    setError: (error) => set({ error }),

    addTask: (task) =>
        set((state) => ({
            columns: state.columns.map((column) =>
                column.id === task.column_id
                    ? { ...column, tasks: sortTasks([...column.tasks, task]) }
                    : column,
            ),
        })),

    updateTask: (task) =>
        set((state) => {
            const columnsWithoutTask = state.columns.map((column) => ({
                ...column,
                tasks: column.tasks.filter((existing) => existing.id !== task.id),
            }));

            return {
                columns: columnsWithoutTask.map((column) =>
                    column.id === task.column_id
                        ? { ...column, tasks: sortTasks([...column.tasks, task]) }
                        : column,
                ),
            };
        }),

    removeTask: (taskId) =>
        set((state) => ({
            columns: state.columns.map((column) => ({
                ...column,
                tasks: column.tasks.filter((task) => task.id !== taskId),
            })),
            activeTaskId:
                state.activeTaskId === taskId ? null : state.activeTaskId,
        })),

    addColumn: (column) =>
        set((state) => ({
            columns: sortColumns([
                ...state.columns,
                { ...column, tasks: column.tasks ?? [] },
            ]),
        })),

    updateColumn: (updatedColumn) =>
        set((state) => ({
            columns: sortColumns(
                state.columns.map((column) =>
                    column.id === updatedColumn.id
                        ? { ...column, ...updatedColumn, tasks: column.tasks }
                        : column,
                ),
            ),
        })),

    removeColumn: (columnId) =>
        set((state) => ({
            columns: state.columns.filter((column) => column.id !== columnId),
        })),

    addComment: (comment) =>
        set((state) => ({
            columns: state.columns.map((column) => ({
                ...column,
                tasks: column.tasks.map((task) =>
                    task.id === comment.task_id &&
                    !task.comments.some((existing) => existing.id === comment.id)
                        ? {
                              ...task,
                              comments: [...task.comments, comment],
                          }
                        : task,
                ),
            })),
        })),

    moveTask: async (taskId, columnId, index) => {
        const previousColumns = get().columns;
        const source = previousColumns
            .flatMap((column) => column.tasks)
            .find((task) => task.id === taskId);
        const destination = previousColumns.find(
            (column) => column.id === columnId,
        );

        if (!source || !destination) {
            return;
        }

        const columnsWithoutTask = previousColumns.map((column) => ({
            ...column,
            tasks: column.tasks.filter((task) => task.id !== taskId),
        }));
        const destinationTasks =
            columnsWithoutTask.find((column) => column.id === columnId)?.tasks ??
            [];
        const targetIndex = Math.max(0, Math.min(index, destinationTasks.length));
        const movedTask = { ...source, column_id: columnId };
        const reorderedTasks = [...destinationTasks];
        reorderedTasks.splice(targetIndex, 0, movedTask);
        const optimisticColumns = columnsWithoutTask.map((column) =>
            column.id === columnId
                ? { ...column, tasks: reorderedTasks }
                : column,
        );

        set({ columns: optimisticColumns, error: null });

        const prevTask = reorderedTasks[targetIndex - 1];
        const nextTask = reorderedTasks[targetIndex + 1];

        try {
            const response = await window.axios.patch<{ task: BoardTask }>(
                route('tasks.move', { task: taskId }),
                {
                    column_id: columnId,
                    prev_id: prevTask?.id ?? null,
                    next_id: nextTask?.id ?? null,
                },
            );

            get().updateTask(response.data.task);
        } catch (error: unknown) {
            set({ columns: previousColumns, error: errorMessage(error) });
        }
    },

    moveColumn: async (columnId, index) => {
        const previousColumns = get().columns;
        const movingColumn = previousColumns.find(
            (column) => column.id === columnId,
        );

        if (!movingColumn) {
            return;
        }

        const reorderedColumns = previousColumns.filter(
            (column) => column.id !== columnId,
        );
        const targetIndex = Math.max(0, Math.min(index, reorderedColumns.length));
        reorderedColumns.splice(targetIndex, 0, movingColumn);
        set({ columns: reorderedColumns, error: null });

        const prevColumn = reorderedColumns[targetIndex - 1];
        const nextColumn = reorderedColumns[targetIndex + 1];

        try {
            const response = await window.axios.patch<{
                column: BoardColumn;
            }>(route('columns.move', { column: columnId }), {
                prev_id: prevColumn?.id ?? null,
                next_id: nextColumn?.id ?? null,
            });

            get().updateColumn(response.data.column);
        } catch (error: unknown) {
            set({ columns: previousColumns, error: errorMessage(error) });
        }
    },
}));
