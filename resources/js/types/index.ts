import type { Ref } from 'vue';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
    active?: boolean;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Toast {
    id: string;
    title: string;
    description?: string;
    variant?: 'default' | 'destructive' | 'success' | 'info' | 'warning';
    duration?: number;
}

export interface UseToastReturn {
    toasts: Ref<Toast[]>;
    toast: (toast: Omit<Toast, 'id'>) => string;
    removeToast: (id: string) => void;
}

export type BreadcrumbItemType = BreadcrumbItem;
