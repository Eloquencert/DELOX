import {
    forwardRef,
    InputHTMLAttributes,
    useEffect,
    useImperativeHandle,
    useRef,
} from 'react';

export default forwardRef(function TextInput(
    {
        type = 'text',
        className = '',
        isFocused = false,
        ...props
    }: InputHTMLAttributes<HTMLInputElement> & { isFocused?: boolean },
    ref,
) {
    const localRef = useRef<HTMLInputElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'rounded-xl border border-[#dcd9d3] bg-[#faf9f7] px-3 py-2.5 text-sm leading-5 text-ink-body shadow-sm placeholder:text-ink-muted/70 focus:border-[#d3cec5] focus:outline-none focus:ring-1 focus:ring-[#e8e4de] ' +
                className
            }
            ref={localRef}
        />
    );
});
