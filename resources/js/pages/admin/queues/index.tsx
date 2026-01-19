import { Head, Link, router } from '@inertiajs/react';
import {
    CheckCircle,
    Clock,
    Eye,
    List,
    PhoneCall,
    SkipForward,
    Trash2,
    XCircle,
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
import type { BreadcrumbItem, Queue, QueueStatus, Service } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Antrian', href: '/admin/queues' },
];

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Statistics {
    total: number;
    waiting: number;
    processing: number;
    completed: number;
    skipped: number;
    cancelled: number;
}

interface Props {
    queues: PaginatedData<Queue>;
    statistics: Statistics;
    services: Service[];
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

export default function QueuesIndex({ queues, statistics, services }: Props) {
    const handleDelete = (queue: Queue) => {
        if (
            confirm(
                `Hapus antrian "${queue.number}"? Tindakan ini tidak dapat dibatalkan.`,
            )
        ) {
            router.delete(`/admin/queues/${queue.id}`);
        }
    };

    const canDelete = (status: QueueStatus) => {
        return !['called', 'processing'].includes(status);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Antrian" />

            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-bold">Kelola Antrian</h1>
                    <p className="text-muted-foreground">
                        Pantau dan kelola antrian hari ini
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <Card>
                        <CardContent className="p-4 text-center">
                            <List className="mx-auto size-6 text-muted-foreground" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.total}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Total
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <Clock className="mx-auto size-6 text-yellow-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.waiting}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Menunggu
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <PhoneCall className="mx-auto size-6 text-blue-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.processing}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Diproses
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <CheckCircle className="mx-auto size-6 text-green-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.completed}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Selesai
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <SkipForward className="mx-auto size-6 text-orange-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.skipped}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Dilewati
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <XCircle className="mx-auto size-6 text-red-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {statistics.cancelled}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Dibatalkan
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Antrian Hari Ini</CardTitle>
                        <CardDescription>
                            {queues.total} antrian total
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {queues.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="px-4 py-3 text-left font-medium">
                                                No. Antrian
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Nama
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Layanan
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Loket
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Waktu Daftar
                                            </th>
                                            <th className="px-4 py-3 text-center font-medium">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {queues.data.map((queue) => (
                                            <tr
                                                key={queue.id}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3">
                                                    <span className="font-mono font-bold">
                                                        {queue.number}
                                                    </span>
                                                    {queue.is_priority && (
                                                        <Badge
                                                            variant="destructive"
                                                            className="ml-2"
                                                        >
                                                            Prioritas
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {queue.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {queue.service?.name ?? '-'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {queue.officer?.user?.name
                                                        ? `${queue.officer.user.name} (Loket ${queue.officer.counter_number})`
                                                        : '-'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Badge
                                                        variant={
                                                            statusConfig[
                                                                queue.status
                                                            ]?.variant ??
                                                            'secondary'
                                                        }
                                                    >
                                                        {statusConfig[
                                                            queue.status
                                                        ]?.label ??
                                                            queue.status}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {new Date(
                                                        queue.created_at,
                                                    ).toLocaleTimeString(
                                                        'id-ID',
                                                        {
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                        },
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-center gap-1">
                                                        <Link
                                                            href={`/admin/queues/${queue.id}`}
                                                        >
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                            >
                                                                <Eye className="size-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            className="text-destructive hover:bg-destructive hover:text-destructive-foreground"
                                                            onClick={() =>
                                                                handleDelete(
                                                                    queue,
                                                                )
                                                            }
                                                            disabled={
                                                                !canDelete(
                                                                    queue.status,
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="py-12 text-center">
                                <List className="mx-auto size-12 text-muted-foreground" />
                                <p className="mt-4 text-muted-foreground">
                                    Belum ada antrian hari ini.
                                </p>
                            </div>
                        )}

                        {queues.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between border-t pt-4">
                                <p className="text-sm text-muted-foreground">
                                    Halaman {queues.current_page} dari{' '}
                                    {queues.last_page}
                                </p>
                                <div className="flex gap-2">
                                    {queues.prev_page_url && (
                                        <Link href={queues.prev_page_url}>
                                            <Button variant="outline" size="sm">
                                                Sebelumnya
                                            </Button>
                                        </Link>
                                    )}
                                    {queues.next_page_url && (
                                        <Link href={queues.next_page_url}>
                                            <Button variant="outline" size="sm">
                                                Selanjutnya
                                            </Button>
                                        </Link>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
