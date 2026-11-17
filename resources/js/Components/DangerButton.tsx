import { ButtonHTMLAttributes } from 'react';

export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={`inline-flex items-center rounded-xl border border-transparent bg-[#c96a50] px-4 py-2.5 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-[#b95e45] focus:outline-none focus:ring-2 focus:ring-[#c96a50]/40 focus:ring-offset-2 focus:ring-offset-canvas active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 ${className}`}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
