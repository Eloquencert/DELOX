import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={`flex w-full items-start border-l-4 py-2 pe-4 ps-3 ${
                active
                    ? 'border-sage bg-sage/15 text-ink focus:border-sage-hover'
                    : 'border-transparent text-ink-muted hover:border-sand hover:bg-sand/10 hover:text-ink-body focus:border-sage focus:bg-sage/10 focus:text-ink-body'
            } text-base font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
