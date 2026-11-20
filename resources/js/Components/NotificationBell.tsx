import Dropdown from '@/Components/Dropdown';
import { AppNotification } from '@/types/delox';
import { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Bell, CheckCheck } from 'lucide-react';

type NotificationPageProps = PageProps<{
    notifications: AppNotification[];
    unreadNotificationsCount: number;
}>;

export default function NotificationBell() {
    const { notifications, unreadNotificationsCount } =
        usePage<NotificationPageProps>().props;

    const openNotification = (notification: AppNotification) => {
        const boardUrl = `${route('projects.board', {
            project: notification.data.project_id,
        })}?task_id=${encodeURIComponent(String(notification.data.task_id))}`;
        const visitBoard = () => router.visit(boardUrl);

        if (notification.read_at) {
            visitBoard();

            return;
        }

        router.patch(
            route('notifications.read', { notification: notification.id }),
            {},
            { onSuccess: visitBoard },
        );
    };

    return (
        <Dropdown>
            <Dropdown.Trigger>
                <button
                    type="button"
                    aria-label={`Notifications, ${unreadNotificationsCount} unread`}
                    aria-haspopup="true"
                    className="relative inline-flex size-10 items-center justify-center rounded-xl text-ink-body transition hover:bg-sand/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage"
                >
                    <Bell size={19} aria-hidden="true" />
                    {unreadNotificationsCount > 0 && (
                        <span className="absolute -right-0.5 -top-0.5 flex min-w-5 items-center justify-center rounded-full bg-[#c96a50] px-1 text-[10px] font-bold leading-5 text-white">
                            {unreadNotificationsCount > 9
                                ? '9+'
                                : unreadNotificationsCount}
                        </span>
                    )}
                </button>
            </Dropdown.Trigger>
            <Dropdown.Content
                width="notification"
                contentClasses="overflow-hidden bg-white"
            >
                <div className="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                    <div>
                        <h2 className="text-sm font-semibold text-ink">
                            Notifications
                        </h2>
                        <p className="text-xs text-ink-muted">
                            {unreadNotificationsCount} unread
                        </p>
                    </div>
                    <button
                        type="button"
                        disabled={unreadNotificationsCount === 0}
                        onClick={() =>
                            router.patch(route('notifications.read-all'), {})
                        }
                        className="inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-ink-muted transition hover:bg-sage/15 hover:text-ink disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <CheckCheck size={14} aria-hidden="true" />
                        Mark all read
                    </button>
                </div>
                <div className="max-h-96 overflow-y-auto">
                    {notifications.length === 0 ? (
                        <p className="px-4 py-8 text-center text-sm text-ink-muted">
                            You are all caught up.
                        </p>
                    ) : (
                        notifications.map((notification) => (
                            <button
                                key={notification.id}
                                type="button"
                                onClick={() => openNotification(notification)}
                                className={`flex w-full items-start gap-3 border-b border-border/70 px-4 py-3 text-left transition last:border-b-0 hover:bg-sand/15 focus-visible:bg-sage/10 focus-visible:outline-none ${
                                    notification.read_at
                                        ? 'bg-white'
                                        : 'bg-sage/5'
                                }`}
                            >
                                <span
                                    className={`mt-1.5 size-2 shrink-0 rounded-full ${
                                        notification.read_at
                                            ? 'bg-border'
                                            : 'bg-sage'
                                    }`}
                                    aria-hidden="true"
                                />
                                <span className="min-w-0 flex-1">
                                    <span className="block text-sm leading-5 text-ink-body">
                                        {notification.data.message}
                                    </span>
                                    <time
                                        dateTime={notification.created_at}
                                        className="mt-1 block text-xs text-ink-muted"
                                    >
                                        {new Date(
                                            notification.created_at,
                                        ).toLocaleString()}
                                    </time>
                                </span>
                            </button>
                        ))
                    )}
                </div>
            </Dropdown.Content>
        </Dropdown>
    );
}
