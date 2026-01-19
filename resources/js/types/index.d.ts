import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    role?: UserRole;
    phone?: string | null;
    nik?: string | null;
    is_active?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

// Queue System Types
export type UserRole =
    | 'admin'
    | 'petugas_umum'
    | 'petugas_posbakum'
    | 'petugas_pembayaran'
    | 'masyarakat';

export type QueueStatus =
    | 'waiting'
    | 'called'
    | 'processing'
    | 'completed'
    | 'skipped'
    | 'cancelled';

export type QueueSource = 'online' | 'kiosk' | 'manual';

export interface Service {
    id: number;
    code: string;
    name: string;
    description: string | null;
    prefix: string;
    average_time: number;
    max_daily_queue: number;
    is_active: boolean;
    requires_documents: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    // Computed attributes
    active_officers_count?: number;
    today_queue_count?: number;
}

export interface Officer {
    id: number;
    user_id: number;
    service_id: number;
    counter_number: number;
    is_active: boolean;
    is_available: boolean;
    max_concurrent: number;
    created_at: string;
    updated_at: string;
    // Relations
    user?: User;
    service?: Service;
    // Computed
    current_queue_count?: number;
}

export interface Queue {
    id: number;
    number: string;
    service_id: number;
    user_id: number | null;
    officer_id: number | null;
    name: string;
    nik: string | null;
    phone: string | null;
    email: string | null;
    is_priority: boolean;
    status: QueueStatus;
    source: QueueSource;
    estimated_time: number | null;
    called_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
    // Relations
    service?: Service;
    user?: User;
    officer?: Officer;
    logs?: QueueLog[];
    // Computed
    waiting_time?: number;
    service_time?: number | null;
}

export interface QueueLog {
    id: number;
    queue_id: number;
    officer_id: number | null;
    from_status: QueueStatus | null;
    to_status: QueueStatus;
    notes: string | null;
    created_at: string;
    // Relations
    officer?: Officer;
}

export interface ServiceSchedule {
    id: number;
    service_id: number;
    day_of_week: number;
    open_time: string;
    close_time: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    // Relations
    service?: Service;
}

export interface QueueStatistics {
    waiting: number;
    called: number;
    processing: number;
    completed: number;
    skipped: number;
    cancelled: number;
    total: number;
    average_wait_time: number | null;
    average_service_time: number | null;
}

// Page Props
export interface OfficerQueueIndexProps {
    officer: Officer;
    waitingQueues: Queue[];
    currentQueues: Queue[];
    statistics: QueueStatistics;
}

export interface OfficerQueueShowProps {
    queue: Queue;
}

export interface DisplayBoardData {
    service: Service;
    current_queues: Array<{
        number: string;
        counter_number: number;
        status: QueueStatus;
    }>;
    waiting_count: number;
    last_updated: string;
}

export interface QueueRegistrationProps {
    services: Service[];
}

export interface QueueTicketProps {
    queue: Queue;
    position: number;
    estimated_wait: number;
}

export interface RecentlyCalledQueue {
    number: string;
    counter: number;
    service_name: string;
    called_at: string;
    voice_url?: string | null;
}
