import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CheckCircle,
    Clock,
    FileText,
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
import type { BreadcrumbItem, Queue, QueueStatus } from '@/types';

interface Props {
    queue: Queue;
}

const statusConfig: Record<
    QueueStatus,
    {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'outline';
    }
> = {
    waiting: { label: 'Menunggu', variant: 'secondary' },
    called: { label: 'Dipanggil', variant: 'default' },
    processing: { label: 'Diproses', variant: 'default' },
    completed: { label: 'Selesai', variant: 'outline' },
    skipped: { label: 'Dilewati', variant: 'destructive' },
    cancelled: { label: 'Dibatalkan', variant: 'destructive' },
};

export default function QueuesShow({ queue }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin' },
        { title: 'Antrian', href: '/admin/queues' },
        { title: queue.number, href: `/admin/queues/${queue.id}` },
    ];

    const formatDateTime = (dateString: string | null) => {
        if (!dateString) {
            return '-';
        }
        return new Date(dateString).toLocaleString('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const formatTime = (dateString: string) => {
        return new Date(dateString).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Antrian - ${queue.number}`} />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/queues">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <div className="flex-1">
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-bold">
                                Antrian {queue.number}
                            </h1>
                            <Badge
                                variant={
                                    statusConfig[queue.status]?.variant ??
                                    'secondary'
                                }
                            >
                                {statusConfig[queue.status]?.label ??
                                    queue.status}
                            </Badge>
                            {queue.is_priority && (
                                <Badge variant="destructive">Prioritas</Badge>
                            )}
                        </div>
                        <p className="text-muted-foreground">
                            Detail informasi antrian
                        </p>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Info Pengunjung */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="size-5" />
                                Informasi Pengunjung
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Nama
                                    </span>
                                    <span className="font-medium">
                                        {queue.name}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        NIK
                                    </span>
                                    <span className="font-medium">
                                        {queue.nik ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Telepon
                                    </span>
                                    <span className="font-medium">
                                        {queue.phone ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Email
                                    </span>
                                    <span className="font-medium">
                                        {queue.email ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Sumber
                                    </span>
                                    <Badge variant="outline">
                                        {queue.source === 'online'
                                            ? 'Online'
                                            : queue.source === 'kiosk'
                                              ? 'Kios'
                                              : 'Manual'}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Info Layanan */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="size-5" />
                                Informasi Layanan
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Layanan
                                    </span>
                                    <span className="font-medium">
                                        {queue.service?.name ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Kode Layanan
                                    </span>
                                    <span className="font-mono font-medium">
                                        {queue.service?.code ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Petugas
                                    </span>
                                    <span className="font-medium">
                                        {queue.officer?.user?.name ?? '-'}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Loket
                                    </span>
                                    <span className="font-medium">
                                        {queue.officer?.counter_number ?? '-'}
                                    </span>
                                </div>
                                {queue.notes && (
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Catatan
                                        </span>
                                        <span className="text-right font-medium">
                                            {queue.notes}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Timeline */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="size-5" />
                                Timeline
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-3">
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Waktu Daftar
                                    </span>
                                    <span className="font-medium">
                                        {formatDateTime(queue.created_at)}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Waktu Panggil
                                    </span>
                                    <span className="font-medium">
                                        {formatDateTime(queue.called_at)}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b pb-2">
                                    <span className="text-muted-foreground">
                                        Waktu Mulai
                                    </span>
                                    <span className="font-medium">
                                        {formatDateTime(queue.started_at)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Waktu Selesai
                                    </span>
                                    <span className="font-medium">
                                        {formatDateTime(queue.completed_at)}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Log Aktivitas */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="size-5" />
                                Log Aktivitas
                            </CardTitle>
                            <CardDescription>
                                Riwayat perubahan status antrian
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {queue.logs && queue.logs.length > 0 ? (
                                <div className="space-y-3">
                                    {queue.logs.map((log) => (
                                        <div
                                            key={log.id}
                                            className="flex items-start gap-3 border-b pb-3 last:border-0"
                                        >
                                            <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                                <CheckCircle className="size-4 text-primary" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="flex items-center gap-2">
                                                    <Badge
                                                        variant={
                                                            statusConfig[
                                                                log.to_status
                                                            ]?.variant ??
                                                            'secondary'
                                                        }
                                                        className="text-xs"
                                                    >
                                                        {statusConfig[
                                                            log.to_status
                                                        ]?.label ??
                                                            log.to_status}
                                                    </Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        {formatTime(
                                                            log.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                                {log.officer?.user?.name && (
                                                    <p className="mt-1 text-sm text-muted-foreground">
                                                        Oleh:{' '}
                                                        {log.officer.user.name}
                                                    </p>
                                                )}
                                                {log.notes && (
                                                    <p className="mt-1 text-sm">
                                                        {log.notes}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-4 text-center text-muted-foreground">
                                    Belum ada log aktivitas.
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
