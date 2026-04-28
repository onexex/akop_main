import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    // IDAGDAG ANG MGA ITO:
    permissions: string[]; // Kung ginagamit mo ang Spatie
    notifications: CustomNotification[]; 
    unread_notifications_count: number;
}

// Mag-define din tayo ng interface para sa Notification mismo
export interface CustomNotification {
    id: number;
    user_id: number;
    title: string;
    message: string;
    url: string | null;
    is_read: boolean;
    created_at: string;}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
