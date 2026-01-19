import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CheckCircle,
    Clock,
    Hash,
    Megaphone,
    Phone,
    User,
} from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type {
    BreadcrumbItem,
    OfficerQueueShowProps,
    QueueStatus,
} from '@/types';

const statusConfig: Record<QueueStatus, { label: string; color: string }> = {
    waiting: {
        label: 'Menunggu',
        color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    },
    called: {
        label: 'Dipanggil',
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    },
    processing: {
        label: 'Diproses',
        color: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    },
    completed: {
        label: 'Selesai',
        color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    },
    skipped: {
        label: 'Dilewati',
        color: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    },
    cancelled: {
        label: 'Dibatalkan',
        color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    },
};

function formatDateTime(dateString: string): string {
    return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

export default function OfficerQueueShow({ queue }: OfficerQueueShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard Petugas', href: '/officer/queues' },
        {
            title: `Antrian ${queue.number}`,
            href: `/officer/queues/${queue.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Antrian ${queue.number}`} />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="icon" asChild>
                            <Link href="/officer/queues">
                                <ArrowLeft className="size-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold">
                                Detail Antrian
                            </h1>
                            <p className="text-muted-foreground">
                                Informasi lengkap antrian {queue.number}
                            </p>
                        </div>
                    </div>
                    <Badge className={statusConfig[queue.status].color}>
                        {statusConfig[queue.status].label}
                    </Badge>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Hash className="size-5" />
                                Informasi Antrian
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center justify-center rounded-lg bg-primary/10 p-6">
                                <span className="text-5xl font-black text-primary">
                                    {queue.number}
                                </span>
                            </div>

                            <div className="space-y-3 text-sm">
                                <div className="flex items-center justify-between rounded-lg border p-3">
                                    <span className="flex items-center gap-2 text-muted-foreground">
                                        <Megaphone className="size-4" />
                                        Layanan
                                    </span>
                                    <span className="font-medium">
                                        {queue.service?.name}
                                    </span>
                                </div>

                                <div className="flex items-center justify-between rounded-lg border p-3">
                                    <span className="flex items-center gap-2 text-muted-foreground">
                                        <User className="size-4" />
                                        Nama
                                    </span>
                                    <span className="font-medium">
                                        {queue.name}
                                    </span>
                                </div>

                                {queue.nik && (
                                    <div className="flex items-center justify-between rounded-lg border p-3">
                                        <span className="flex items-center gap-2 text-muted-foreground">
                                            <Hash className="size-4" />
                                            NIK
                                        </span>
                                        <span className="font-mono font-medium">
                                            {queue.nik}
                                        </span>
                                    </div>
                                )}

                                {queue.phone && (
                                    <div className="flex items-center justify-between rounded-lg border p-3">
                                        <span className="flex items-center gap-2 text-muted-foreground">
                                            <Phone className="size-4" />
                                            Telepon
                                        </span>
                                        <span className="font-medium">
                                            {queue.phone}
                                        </span>
                                    </div>
                                )}

                                <div className="flex items-center justify-between rounded-lg border p-3">
                                    <span className="flex items-center gap-2 text-muted-foreground">
                                        <Calendar className="size-4" />
                                        Waktu Daftar
                                    </span>
                                    <span className="font-medium">
                                        {formatDateTime(queue.created_at)}
                                    </span>
                                </div>

                                <div className="flex items-center justify-between rounded-lg border p-3">
                                    <span className="text-muted-foreground">
                                        Sumber
                                    </span>
                                    <Badge
                                        variant="outline"
                                        className="capitalize"
                                    >
                                        {queue.source}
                                    </Badge>
                                </div>

                                {queue.is_priority && (
                                    <div className="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-900/20">
                                        <span className="text-red-800 dark:text-red-300">
                                            Prioritas
                                        </span>
                                        <Badge variant="destructive">Ya</Badge>
                                    </div>
                                )}

                                {queue.officer && (
                                    <div className="flex items-center justify-between rounded-lg border p-3">
                                        <span className="text-muted-foreground">
                                            Petugas
                                        </span>
                                        <span className="font-medium">
                                            {queue.officer.user?.name} (Loket{' '}
                                            {queue.officer.counter_number})
                                        </span>
                                    </div>
                                )}

                                {queue.notes && (
                                    <div className="rounded-lg border p-3">
                                        <span className="text-muted-foreground">
                                            Catatan
                                        </span>
                                        <p className="mt-1 font-medium">
                                            {queue.notes}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="size-5" />
                                Riwayat Status
                            </CardTitle>
                            <CardDescription>
                                Perubahan status antrian
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {queue.logs && queue.logs.length > 0 ? (
                                <div className="relative space-y-4">
                                    <div className="absolute top-0 left-3.5 h-full w-0.5 bg-border" />
                                    {queue.logs.map((log, index) => (
                                        <div
                                            key={log.id}
                                            className="relative flex gap-4 pl-8"
                                        >
                                            <div className="absolute left-0 flex size-7 items-center justify-center rounded-full border bg-background">
                                                {log.to_status ===
                                                'completed' ? (
                                                    <CheckCircle className="size-4 text-green-500" />
                                                ) : (
                                                    <div className="size-2 rounded-full bg-primary" />
                                                )}
                                            </div>
                                            <div className="flex-1 rounded-lg border p-3">
                                                <div className="flex items-center justify-between">
                                                    <Badge
                                                        className={
                                                            statusConfig[
                                                                log.to_status
                                                            ].color
                                                        }
                                                    >
                                                        {
                                                            statusConfig[
                                                                log.to_status
                                                            ].label
                                                        }
                                                    </Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        {formatTime(
                                                            log.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                                {log.from_status && (
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        dari{' '}
                                                        {
                                                            statusConfig[
                                                                log.from_status
                                                            ].label
                                                        }
                                                    </p>
                                                )}
                                                {log.officer && (
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        oleh{' '}
                                                        {log.officer.user?.name}
                                                    </p>
                                                )}
                                                {log.notes && (
                                                    <p className="mt-2 text-sm">
                                                        {log.notes}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-muted-foreground">
                                    Belum ada riwayat status.
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Timeline</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 text-sm md:grid-cols-4">
                            <div className="rounded-lg border p-4">
                                <p className="text-muted-foreground">
                                    Waktu Daftar
                                </p>
                                <p className="mt-1 font-medium">
                                    {formatTime(queue.created_at)}
                                </p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <p className="text-muted-foreground">
                                    Waktu Panggil
                                </p>
                                <p className="mt-1 font-medium">
                                    {queue.called_at
                                        ? formatTime(queue.called_at)
                                        : '-'}
                                </p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <p className="text-muted-foreground">
                                    Waktu Mulai Proses
                                </p>
                                <p className="mt-1 font-medium">
                                    {queue.started_at
                                        ? formatTime(queue.started_at)
                                        : '-'}
                                </p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <p className="text-muted-foreground">
                                    Waktu Selesai
                                </p>
                                <p className="mt-1 font-medium">
                                    {queue.completed_at
                                        ? formatTime(queue.completed_at)
                                        : '-'}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
