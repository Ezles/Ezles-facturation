import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Crée une version "debounced" d'une fonction qui ne sera exécutée
 * qu'après un certain délai d'inactivité.
 * 
 * @param fn La fonction à debouncer
 * @param delay Le délai en millisecondes
 * @returns Une fonction debounced
 */
export function debounce<T extends (...args: any[]) => any>(
    fn: T,
    delay: number
): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    
    return function(...args: Parameters<T>) {
        if (timeout) {
            clearTimeout(timeout);
        }
        
        timeout = setTimeout(() => {
            fn(...args);
            timeout = null;
        }, delay);
    };
}
