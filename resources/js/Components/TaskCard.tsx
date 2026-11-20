import { BoardTask } from '@/types/delox';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { CalendarDays, MessageSquare } from 'lucide-react';

const priorityStyles: Record<BoardTask['priority'], string> = {
    low: 'border-[#9dbeb5]/50 bg-[#9dbeb5]/25 text-[#25443c]',
    medium: 'border-[#d6d3cd] bg-[#efefef] text-[#373d3a]',
    high: 'border-[#d8b9a9]/70 bg-[#d8b9a9]/35 text-[#5a3626]',
    urgent: 'border-[#c96a50]/40 bg-[#c96a50]/15 text-[#8f3822]',
};

interface TaskCardProps {
    task: BoardTask;
    projectKey: string;
    canDrag: boolean;
    onOpen: (task: BoardTask) => void;
}

export default function TaskCard({
    task,
    projectKey,
    canDrag,
    onOpen,
}: TaskCardProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: `task-${task.id}`,
        data: { type: 'task', taskId: task.id, columnId: task.column_id },
        disabled: !canDrag,
    });

    return (
        <button
            ref={setNodeRef}
            type="button"
            style={{
                transform: CSS.Transform.toString(transform),
                transition,
            }}
            onClick={() => onOpen(task)}
            {...attributes}
            {...listeners}
            className={`w-full rounded-xl border border-border-subtle bg-white p-3.5 text-left shadow-[0_1px_4px_rgba(31,36,33,0.03)] transition-all hover:border-sage hover:shadow-[0_4px_12px_rgba(31,36,33,0.06)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage ${
                isDragging
                    ? 'rotate-[1deg] opacity-95 ring-2 ring-sage shadow-xl'
                    : ''
            }`}
            aria-label={`Open ${projectKey}-${task.number}: ${task.title}`}
        >
            <div className="mb-2 flex items-center justify-between gap-2">
                <span className="font-mono text-xs font-medium tabular-nums text-ink-muted">
                    {projectKey}-{task.number}
                </span>
                <span
                    className={`rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize tracking-wide ${priorityStyles[task.priority]}`}
                >
                    {task.priority}
                </span>
            </div>
            <p className="text-sm font-medium leading-5 text-ink">
                {task.title}
            </p>
            <div className="mt-3 flex items-center justify-between gap-2 text-xs text-ink-muted">
                <div className="flex items-center gap-3">
                    {task.due_date && (
                        <span className="inline-flex items-center gap-1 rounded-full bg-canvas px-2 py-1 tabular-nums">
                            <CalendarDays size={13} aria-hidden="true" />
                            {task.due_date}
                        </span>
                    )}
                    {task.comments.length > 0 && (
                        <span className="inline-flex items-center gap-1 tabular-nums">
                            <MessageSquare size={13} aria-hidden="true" />
                            {task.comments.length}
                        </span>
                    )}
                </div>
                {task.assignee && (
                    <span
                        className="flex size-6 items-center justify-center rounded-full bg-sage/45 text-[10px] font-semibold text-ink"
                        title={task.assignee.name}
                    >
                        {task.assignee.name.slice(0, 1).toUpperCase()}
                    </span>
                )}
            </div>
        </button>
    );
}
