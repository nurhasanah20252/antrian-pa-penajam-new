import { Head, Link, router } from '@inertiajs/react';
import {
    BarChart3,
    Calendar,
    CheckCircle,
    Clock,
    Download,
    Filter,
    List,
    SkipForward,
    XCircle,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Queue, QueueStatus, Service } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Laporan', href: '/admin/reports' },
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

interface Summary {
    total: number;
    completed: number;
    cancelled: number;
    skipped: number;
    average_wait_time: number | null;
    average_service_time: number | null;
}

interface DailyStat {
    date: string;
    total: number;
    completed: number;
}

interface Filters {
    start_date: string;
    end_date: string;
    service_id: string | null;
}

interface Props {
    queues: PaginatedData<Queue>;
    summary: Summary;
    dailyStats: DailyStat[];
    services: Service[];
    filters: Filters;
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

export default function ReportsIndex({
    queues,
    summary,
    dailyStats,
    services,
    filters,
}: Props) {
    const [filterValues, setFilterValues] = useState({
        start_date: filters.start_date,
        end_date: filters.end_date,
        service_id: filters.service_id ?? '',
    });

    const handleFilter = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        router.get('/admin/reports', filterValues, { preserveState: true });
    };

    const handleExport = () => {
        const params = new URLSearchParams({
            start_date: filterValues.start_date,
            end_date: filterValues.end_date,
        });
        if (filterValues.service_id) {
            params.append('service_id', filterValues.service_id);
        }
        window.location.href = `/admin/reports/export?${params.toString()}`;
    };

    const completionRate =
        summary.total > 0
            ? Math.round((summary.completed / summary.total) * 100)
            : 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Laporan Antrian" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Laporan Antrian</h1>
                        <p className="text-muted-foreground">
                            Analisis dan ekspor data antrian
                        </p>
                    </div>
                    <Button onClick={handleExport}>
                        <Download className="mr-2 size-4" />
                        Ekspor CSV
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="size-5" />
                            Filter Laporan
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={handleFilter}
                            className="flex flex-wrap items-end gap-4"
                        >
                            <div className="space-y-2">
                                <Label htmlFor="start_date">
                                    Tanggal Mulai
                                </Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    value={filterValues.start_date}
                                    onChange={(e) =>
                                        setFilterValues({
                                            ...filterValues,
                                            start_date: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="end_date">Tanggal Akhir</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    value={filterValues.end_date}
                                    onChange={(e) =>
                                        setFilterValues({
                                            ...filterValues,
                                            end_date: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="service_id">Layanan</Label>
                                <select
                                    id="service_id"
                                    value={filterValues.service_id}
                                    onChange={(e) =>
                                        setFilterValues({
                                            ...filterValues,
                                            service_id: e.target.value,
                                        })
                                    }
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="">Semua Layanan</option>
                                    {services.map((service) => (
                                        <option
                                            key={service.id}
                                            value={service.id}
                                        >
                                            {service.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <Button type="submit">
                                <Filter className="mr-2 size-4" />
                                Terapkan
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <Card>
                        <CardContent className="p-4 text-center">
                            <List className="mx-auto size-6 text-muted-foreground" />
                            <p className="mt-2 text-2xl font-bold">
                                {summary.total}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Total Antrian
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <CheckCircle className="mx-auto size-6 text-green-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {summary.completed}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Selesai
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <BarChart3 className="mx-auto size-6 text-blue-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {completionRate}%
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Tingkat Selesai
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <Clock className="mx-auto size-6 text-yellow-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {summary.average_wait_time
                                    ? `${Math.round(summary.average_wait_time)} mnt`
                                    : '-'}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Rata-rata Tunggu
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4 text-center">
                            <SkipForward className="mx-auto size-6 text-orange-500" />
                            <p className="mt-2 text-2xl font-bold">
                                {summary.skipped}
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
                                {summary.cancelled}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Dibatalkan
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {dailyStats.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="size-5" />
                                Statistik Harian
                            </CardTitle>
                            <CardDescription>
                                Jumlah antrian per hari dalam periode terpilih
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="px-4 py-3 text-left font-medium">
                                                Tanggal
                                            </th>
                                            <th className="px-4 py-3 text-right font-medium">
                                                Total
                                            </th>
                                            <th className="px-4 py-3 text-right font-medium">
                                                Selesai
                                            </th>
                                            <th className="px-4 py-3 text-right font-medium">
                                                Tingkat Selesai
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {dailyStats.map((stat) => (
                                            <tr
                                                key={stat.date}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-4 py-3">
                                                    {new Date(
                                                        stat.date,
                                                    ).toLocaleDateString(
                                                        'id-ID',
                                                        {
                                                            weekday: 'short',
                                                            day: 'numeric',
                                                            month: 'short',
                                                            year: 'numeric',
                                                        },
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-right font-medium">
                                                    {stat.total}
                                                </td>
                                                <td className="px-4 py-3 text-right font-medium text-green-600">
                                                    {stat.completed}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <Badge variant="outline">
                                                        {stat.total > 0
                                                            ? Math.round(
                                                                  (stat.completed /
                                                                      stat.total) *
                                                                      100,
                                                              )
                                                            : 0}
                                                        %
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Detail Antrian</CardTitle>
                        <CardDescription>
                            {queues.total} antrian dalam periode terpilih
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
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Petugas
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Tanggal
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
                                                </td>
                                                <td className="px-4 py-3">
                                                    {queue.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {queue.service?.name ?? '-'}
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
                                                <td className="px-4 py-3">
                                                    {queue.officer?.user
                                                        ?.name ?? '-'}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {new Date(
                                                        queue.created_at,
                                                    ).toLocaleDateString(
                                                        'id-ID',
                                                        {
                                                            day: 'numeric',
                                                            month: 'short',
                                                            year: 'numeric',
                                                        },
                                                    )}
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
                                    Tidak ada data untuk periode terpilih.
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
