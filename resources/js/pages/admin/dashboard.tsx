import { Head, Link } from '@inertiajs/react';
import { BarChart3, CheckCircle, Clock, Settings, Users } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem, Queue, Service } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin' },
];

interface TodayStats {
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

interface ServiceStat extends Service {
    today_total: number;
    today_waiting: number;
    today_completed: number;
}

interface ActiveOfficer {
    id: number;
    name: string;
    service: string;
    counter: number;
    current_queue: string | null;
}

interface WeeklyStat {
    date: string;
    label: string;
    total: number;
    completed: number;
}

interface Counts {
    services: number;
    officers: number;
    users: number;
    queues_today: number;
}

interface Props {
    todayStats: TodayStats;
    serviceStats: ServiceStat[];
    activeOfficers: ActiveOfficer[];
    recentQueues: Queue[];
    weeklyStats: WeeklyStat[];
    counts: Counts;
}

function StatCard({
    title,
    value,
    icon: Icon,
    variant = 'default',
    href,
}: {
    title: string;
    value: number | string;
    icon: React.ElementType;
    variant?: 'default' | 'warning' | 'success' | 'info' | 'danger';
    href?: string;
}) {
    const variantClasses = {
        default: 'bg-muted/50',
        warning: 'bg-yellow-50 dark:bg-yellow-950/20',
        success: 'bg-green-50 dark:bg-green-950/20',
        info: 'bg-blue-50 dark:bg-blue-950/20',
        danger: 'bg-red-50 dark:bg-red-950/20',
    };

    const iconClasses = {
        default: 'text-muted-foreground',
        warning: 'text-yellow-600 dark:text-yellow-400',
        success: 'text-green-600 dark:text-green-400',
        info: 'text-blue-600 dark:text-blue-400',
        danger: 'text-red-600 dark:text-red-400',
    };

    const content = (
        <Card className={cn('py-4', variantClasses[variant])}>
            <CardContent className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-muted-foreground">{title}</p>
                    <p className="text-3xl font-bold">{value}</p>
                </div>
                <Icon className={cn('size-8', iconClasses[variant])} />
            </CardContent>
        </Card>
    );

    if (href) {
        return (
            <Link
                href={href}
                className="block transition-opacity hover:opacity-80"
            >
                {content}
            </Link>
        );
    }

    return content;
}

function StatusBadge({ status }: { status: Queue['status'] }) {
    const config = {
        waiting: { label: 'Menunggu', variant: 'secondary' as const },
        called: { label: 'Dipanggil', variant: 'default' as const },
        processing: { label: 'Diproses', variant: 'default' as const },
        completed: { label: 'Selesai', variant: 'secondary' as const },
        skipped: { label: 'Dilewati', variant: 'outline' as const },
        cancelled: { label: 'Dibatalkan', variant: 'destructive' as const },
    };

    const { label, variant } = config[status];
    return <Badge variant={variant}>{label}</Badge>;
}

export default function AdminDashboard({
    todayStats,
    serviceStats,
    activeOfficers,
    recentQueues,
    weeklyStats,
    counts,
}: Props) {
    const maxWeeklyTotal = Math.max(...weeklyStats.map((s) => s.total), 1);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-bold">Admin Dashboard</h1>
                    <p className="text-muted-foreground">
                        Kelola sistem antrian PTSP
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard
                        title="Layanan"
                        value={counts.services}
                        icon={Settings}
                        href="/admin/services"
                    />
                    <StatCard
                        title="Petugas"
                        value={counts.officers}
                        icon={Users}
                        href="/admin/officers"
                    />
                    <StatCard
                        title="Antrian Hari Ini"
                        value={counts.queues_today}
                        icon={Clock}
                        variant="info"
                        href="/admin/queues"
                    />
                    <StatCard
                        title="Selesai"
                        value={todayStats.completed}
                        icon={CheckCircle}
                        variant="success"
                    />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BarChart3 className="size-5" />
                                Statistik Hari Ini
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-3 gap-4 text-center">
                                <div className="rounded-lg border bg-yellow-50 p-3 dark:bg-yellow-950/20">
                                    <p className="text-sm text-muted-foreground">
                                        Menunggu
                                    </p>
                                    <p className="text-2xl font-bold text-yellow-600">
                                        {todayStats.waiting}
                                    </p>
                                </div>
                                <div className="rounded-lg border bg-blue-50 p-3 dark:bg-blue-950/20">
                                    <p className="text-sm text-muted-foreground">
                                        Diproses
                                    </p>
                                    <p className="text-2xl font-bold text-blue-600">
                                        {todayStats.called +
                                            todayStats.processing}
                                    </p>
                                </div>
                                <div className="rounded-lg border bg-green-50 p-3 dark:bg-green-950/20">
                                    <p className="text-sm text-muted-foreground">
                                        Selesai
                                    </p>
                                    <p className="text-2xl font-bold text-green-600">
                                        {todayStats.completed}
                                    </p>
                                </div>
                                <div className="rounded-lg border p-3">
                                    <p className="text-sm text-muted-foreground">
                                        Dilewati
                                    </p>
                                    <p className="text-2xl font-bold">
                                        {todayStats.skipped}
                                    </p>
                                </div>
                                <div className="rounded-lg border bg-red-50 p-3 dark:bg-red-950/20">
                                    <p className="text-sm text-muted-foreground">
                                        Dibatalkan
                                    </p>
                                    <p className="text-2xl font-bold text-red-600">
                                        {todayStats.cancelled}
                                    </p>
                                </div>
                                <div className="rounded-lg border p-3">
                                    <p className="text-sm text-muted-foreground">
                                        Total
                                    </p>
                                    <p className="text-2xl font-bold">
                                        {todayStats.total}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 grid grid-cols-2 gap-4 border-t pt-4">
                                <div className="text-center">
                                    <p className="text-sm text-muted-foreground">
                                        Rata-rata Tunggu
                                    </p>
                                    <p className="text-xl font-semibold">
                                        {todayStats.average_wait_time ?? '-'}{' '}
                                        menit
                                    </p>
                                </div>
                                <div className="text-center">
                                    <p className="text-sm text-muted-foreground">
                                        Rata-rata Layanan
                                    </p>
                                    <p className="text-xl font-semibold">
                                        {todayStats.average_service_time ?? '-'}{' '}
                                        menit
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Statistik Per Layanan</CardTitle>
                            <CardDescription>
                                Antrian hari ini per layanan
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {serviceStats.map((service) => (
                                    <div
                                        key={service.id}
                                        className="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {service.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                Kode: {service.code}
                                            </p>
                                        </div>
                                        <div className="flex gap-4 text-center">
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Total
                                                </p>
                                                <p className="font-bold">
                                                    {service.today_total}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Menunggu
                                                </p>
                                                <p className="font-bold text-yellow-600">
                                                    {service.today_waiting}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Selesai
                                                </p>
                                                <p className="font-bold text-green-600">
                                                    {service.today_completed}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                {serviceStats.length === 0 && (
                                    <p className="py-4 text-center text-muted-foreground">
                                        Tidak ada layanan aktif.
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Grafik Mingguan</CardTitle>
                            <CardDescription>
                                Antrian 7 hari terakhir
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex h-40 items-end justify-between gap-2">
                                {weeklyStats.map((stat) => (
                                    <div
                                        key={stat.date}
                                        className="flex flex-1 flex-col items-center gap-1"
                                    >
                                        <div className="relative flex w-full flex-col items-center">
                                            <div
                                                className="w-full rounded-t bg-primary/20"
                                                style={{
                                                    height: `${(stat.total / maxWeeklyTotal) * 120}px`,
                                                }}
                                            />
                                            <div
                                                className="absolute bottom-0 w-full rounded-t bg-primary"
                                                style={{
                                                    height: `${(stat.completed / maxWeeklyTotal) * 120}px`,
                                                }}
                                            />
                                        </div>
                                        <span className="text-xs text-muted-foreground">
                                            {stat.label}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 flex justify-center gap-6 text-sm">
                                <div className="flex items-center gap-2">
                                    <div className="size-3 rounded bg-primary/20" />
                                    <span>Total</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div className="size-3 rounded bg-primary" />
                                    <span>Selesai</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="size-5" />
                                Petugas Aktif
                            </CardTitle>
                            <CardDescription>
                                Petugas yang sedang bertugas
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {activeOfficers.map((officer) => (
                                    <div
                                        key={officer.id}
                                        className="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {officer.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {officer.service} - Loket{' '}
                                                {officer.counter}
                                            </p>
                                        </div>
                                        {officer.current_queue ? (
                                            <Badge variant="default">
                                                {officer.current_queue}
                                            </Badge>
                                        ) : (
                                            <Badge variant="secondary">
                                                Idle
                                            </Badge>
                                        )}
                                    </div>
                                ))}
                                {activeOfficers.length === 0 && (
                                    <p className="py-4 text-center text-muted-foreground">
                                        Tidak ada petugas aktif.
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Antrian Terbaru</CardTitle>
                                <CardDescription>
                                    10 antrian terakhir hari ini
                                </CardDescription>
                            </div>
                            <Link
                                href="/admin/queues"
                                className="text-sm text-primary hover:underline"
                            >
                                Lihat Semua
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b">
                                        <th className="pb-2 text-left font-medium">
                                            Nomor
                                        </th>
                                        <th className="pb-2 text-left font-medium">
                                            Nama
                                        </th>
                                        <th className="pb-2 text-left font-medium">
                                            Layanan
                                        </th>
                                        <th className="pb-2 text-left font-medium">
                                            Petugas
                                        </th>
                                        <th className="pb-2 text-left font-medium">
                                            Status
                                        </th>
                                        <th className="pb-2 text-left font-medium">
                                            Waktu
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentQueues.map((queue) => (
                                        <tr key={queue.id} className="border-b">
                                            <td className="py-2 font-mono font-bold">
                                                {queue.number}
                                            </td>
                                            <td className="py-2">
                                                {queue.name}
                                            </td>
                                            <td className="py-2">
                                                {queue.service?.name ?? '-'}
                                            </td>
                                            <td className="py-2">
                                                {queue.officer?.user?.name ??
                                                    '-'}
                                            </td>
                                            <td className="py-2">
                                                <StatusBadge
                                                    status={queue.status}
                                                />
                                            </td>
                                            <td className="py-2 text-muted-foreground">
                                                {new Date(
                                                    queue.created_at,
                                                ).toLocaleTimeString('id-ID', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </td>
                                        </tr>
                                    ))}
                                    {recentQueues.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={6}
                                                className="py-8 text-center text-muted-foreground"
                                            >
                                                Belum ada antrian hari ini.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
