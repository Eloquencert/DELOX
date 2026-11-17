import { InputHTMLAttributes } from 'react';

export default function Checkbox({
    className = '',
    ...props
}: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-[#dcd9d3] text-sage shadow-sm focus:ring-sage/40 ' +
                className
            }
        />
    );
}
