import { ProjectActivity } from '@/types/delox';
import { X } from 'lucide-react';

interface ActivityFeedProps {
    activities: ProjectActivity[];
    onClose: () => void;
    onSelectTask: (taskId: number) => void;
}

export default function ActivityFeed({
    activities,
    onClose,
    onSelectTask,
}: ActivityFeedProps) {
    return (
        <div
            className="fixed inset-0 z-[70] flex justify-end bg-ink/25 backdrop-blur-[1px]"
            role="presentation"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) {
                    onClose();
                }
            }}
        >
            <aside
                role="dialog"
                aria-modal="true"
                aria-labelledby="activity-feed-title"
                className="flex h-full w-full max-w-md flex-col border-l border-border bg-white shadow-2xl"
            >
                <header className="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                    <div>
                        <h2
                            id="activity-feed-title"
                            className="text-base font-semibold text-ink"
                        >
                            Activity history
                        </h2>
                        <p className="mt-1 text-sm text-ink-muted">
                            Recent changes across this project
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Close activity history"
                        className="rounded-lg p-2 text-ink-muted transition hover:bg-sand/20 hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                    >
                        <X size={18} aria-hidden="true" />
                    </button>
                </header>
                <div className="flex-1 overflow-y-auto px-5 py-2">
                    {activities.length === 0 ? (
                        <p className="py-8 text-center text-sm text-ink-muted">
                            Project activity will appear here.
                        </p>
                    ) : (
                        <ol className="divide-y divide-border/70">
                            {activities.map((activity) => {
                                const taskId = activity.task_id;

                                return (
                                    <li key={activity.id} className="py-4">
                                        <p className="text-sm leading-5 text-ink-body">
                                            <span className="font-semibold text-ink">
                                                {activity.user?.name ??
                                                    'Former member'}
                                            </span>{' '}
                                            {activity.description}
                                        </p>
                                        <time
                                            dateTime={activity.created_at}
                                            className="mt-1.5 block text-xs text-ink-muted"
                                        >
                                            {new Date(
                                                activity.created_at,
                                            ).toLocaleString()}
                                        </time>
                                        {taskId !== null && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    onSelectTask(taskId)
                                                }
                                                className="mt-2 text-xs font-semibold text-[#496d61] underline decoration-[#9dbeb5] underline-offset-2 transition hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                                            >
                                                Open task
                                            </button>
                                        )}
                                    </li>
                                );
                            })}
                        </ol>
                    )}
                </div>
            </aside>
        </div>
    );
}
