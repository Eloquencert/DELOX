import deloxLogo from '../../image/DELOX.png';
import { ImgHTMLAttributes } from 'react';

export default function ApplicationLogo({
    alt = 'delox',
    className = '',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <span
            className={`inline-flex shrink-0 items-center justify-center overflow-hidden ${className}`}
        >
            <img
                {...props}
                src={deloxLogo}
                alt={alt}
                className="h-[300%] w-auto max-w-none object-contain"
            />
        </span>
    );
}
