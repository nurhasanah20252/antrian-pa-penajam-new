import { Head, router, usePoll } from '@inertiajs/react';
import {
    CheckCircle,
    Clock,
    PhoneCall,
    PlayCircle,
    RefreshCw,
    SkipForward,
    Users,
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
import { cn } from '@/lib/utils';
import type {
    BreadcrumbItem,
    Officer,
    OfficerQueueIndexProps,
    Queue,
} from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Petugas', href: '/officer/queues' },
];

function StatCard({
    title,
    value,
    icon: Icon,
    variant = 'default',
}: {
    title: string;
    value: number;
    icon: React.ElementType;
    variant?: 'default' | 'warning' | 'success' | 'info';
}) {
    const variantClasses = {
        default: 'bg-muted/50',
        warning: 'bg-yellow-50 dark:bg-yellow-950/20',
        success: 'bg-green-50 dark:bg-green-950/20',
        info: 'bg-blue-50 dark:bg-blue-950/20',
    };

    const iconClasses = {
        default: 'text-muted-foreground',
        warning: 'text-yellow-600 dark:text-yellow-400',
        success: 'text-green-600 dark:text-green-400',
        info: 'text-blue-600 dark:text-blue-400',
    };

    return (
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

function CurrentQueueCard({
    queue,
    officer,
    onRecall,
    onProcess,
    onComplete,
    onSkip,
}: {
    queue: Queue;
    officer: Officer;
    onRecall: () => void;
    onProcess: () => void;
    onComplete: () => void;
    onSkip: () => void;
}) {
    const isCalled = queue.status === 'called';
    const isProcessing = queue.status === 'processing';

    return (
        <Card className="border-2 border-primary">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardDescription>
                            Loket {officer.counter_number}
                        </CardDescription>
                        <CardTitle className="text-4xl font-bold">
                            {queue.number}
                        </CardTitle>
                    </div>
                    <StatusBadge status={queue.status} />
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="text-sm text-muted-foreground">
                    <p>
                        <strong>Nama:</strong> {queue.name}
                    </p>
                    {queue.is_priority && (
                        <Badge variant="destructive" className="mt-1">
                            Prioritas
                        </Badge>
                    )}
                </div>

                <div className="flex flex-wrap gap-2">
                    {isCalled && (
                        <>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={onRecall}
                            >
                                <RefreshCw className="mr-1 size-4" />
                                Panggil Ulang
                            </Button>
                            <Button size="sm" onClick={onProcess}>
                                <PlayCircle className="mr-1 size-4" />
                                Mulai Proses
                            </Button>
                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={onSkip}
                            >
                                <SkipForward className="mr-1 size-4" />
                                Lewati
                            </Button>
                        </>
                    )}
                    {isProcessing && (
                        <>
                            <Button size="sm" onClick={onComplete}>
                                <CheckCircle className="mr-1 size-4" />
                                Selesai
                            </Button>
                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={onSkip}
                            >
                                <SkipForward className="mr-1 size-4" />
                                Lewati
                            </Button>
                        </>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

function WaitingQueueItem({
    queue,
    onCall,
}: {
    queue: Queue;
    onCall: () => void;
}) {
    return (
        <div className="flex items-center justify-between rounded-lg border p-3">
            <div className="flex items-center gap-3">
                <span className="text-lg font-bold">{queue.number}</span>
                <span className="text-sm text-muted-foreground">
                    {queue.name}
                </span>
                {queue.is_priority && (
                    <Badge variant="destructive" className="text-xs">
                        Prioritas
                    </Badge>
                )}
            </div>
            <Button variant="outline" size="sm" onClick={onCall}>
                <PhoneCall className="mr-1 size-4" />
                Panggil
            </Button>
        </div>
    );
}

export default function OfficerQueueIndex({
    officer,
    waitingQueues,
    currentQueues,
    statistics,
}: OfficerQueueIndexProps) {
    usePoll(5000);

    const handleCallNext = () => {
        router.post('/officer/queues/call-next', {}, { preserveScroll: true });
    };

    const handleCallQueue = (queueId: number) => {
        router.post(
            `/officer/queues/${queueId}/call`,
            {},
            { preserveScroll: true },
        );
    };

    const handleRecall = (queueId: number) => {
        router.post(
            `/officer/queues/${queueId}/recall`,
            {},
            { preserveScroll: true },
        );
    };

    const handleProcess = (queueId: number) => {
        router.post(
            `/officer/queues/${queueId}/process`,
            {},
            { preserveScroll: true },
        );
    };

    const handleComplete = (queueId: number) => {
        router.post(
            `/officer/queues/${queueId}/complete`,
            {},
            { preserveScroll: true },
        );
    };

    const handleSkip = (queueId: number) => {
        router.post(
            `/officer/queues/${queueId}/skip`,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard Petugas" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Dashboard Petugas
                        </h1>
                        <p className="text-muted-foreground">
                            {officer.service?.name} - Loket{' '}
                            {officer.counter_number}
                        </p>
                    </div>
                    <Button
                        onClick={handleCallNext}
                        disabled={waitingQueues.length === 0}
                        size="lg"
                    >
                        <PhoneCall className="mr-2 size-5" />
                        Panggil Berikutnya
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard
                        title="Menunggu"
                        value={statistics.waiting}
                        icon={Clock}
                        variant="warning"
                    />
                    <StatCard
                        title="Dipanggil"
                        value={statistics.called}
                        icon={PhoneCall}
                        variant="info"
                    />
                    <StatCard
                        title="Diproses"
                        value={statistics.processing}
                        icon={PlayCircle}
                        variant="info"
                    />
                    <StatCard
                        title="Selesai"
                        value={statistics.completed}
                        icon={CheckCircle}
                        variant="success"
                    />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="size-5" />
                                Antrian Saat Ini
                            </CardTitle>
                            <CardDescription>
                                Antrian yang sedang Anda tangani
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {currentQueues.length === 0 ? (
                                <p className="py-8 text-center text-muted-foreground">
                                    Tidak ada antrian aktif. Klik "Panggil
                                    Berikutnya" untuk memulai.
                                </p>
                            ) : (
                                <div className="space-y-4">
                                    {currentQueues.map((queue) => (
                                        <CurrentQueueCard
                                            key={queue.id}
                                            queue={queue}
                                            officer={officer}
                                            onRecall={() =>
                                                handleRecall(queue.id)
                                            }
                                            onProcess={() =>
                                                handleProcess(queue.id)
                                            }
                                            onComplete={() =>
                                                handleComplete(queue.id)
                                            }
                                            onSkip={() => handleSkip(queue.id)}
                                        />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="size-5" />
                                Daftar Tunggu
                            </CardTitle>
                            <CardDescription>
                                {waitingQueues.length} antrian menunggu
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {waitingQueues.length === 0 ? (
                                <p className="py-8 text-center text-muted-foreground">
                                    Tidak ada antrian menunggu.
                                </p>
                            ) : (
                                <div className="max-h-96 space-y-2 overflow-y-auto">
                                    {waitingQueues.map((queue) => (
                                        <WaitingQueueItem
                                            key={queue.id}
                                            queue={queue}
                                            onCall={() =>
                                                handleCallQueue(queue.id)
                                            }
                                        />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Statistik Hari Ini</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 text-sm md:grid-cols-5">
                            <div className="rounded-lg border p-3 text-center">
                                <p className="text-muted-foreground">Total</p>
                                <p className="text-2xl font-bold">
                                    {statistics.total}
                                </p>
                            </div>
                            <div className="rounded-lg border p-3 text-center">
                                <p className="text-muted-foreground">
                                    Dilewati
                                </p>
                                <p className="text-2xl font-bold">
                                    {statistics.skipped}
                                </p>
                            </div>
                            <div className="rounded-lg border p-3 text-center">
                                <p className="text-muted-foreground">
                                    Dibatalkan
                                </p>
                                <p className="text-2xl font-bold">
                                    {statistics.cancelled}
                                </p>
                            </div>
                            <div className="rounded-lg border p-3 text-center">
                                <p className="text-muted-foreground">
                                    Rata-rata Tunggu
                                </p>
                                <p className="text-2xl font-bold">
                                    {statistics.average_wait_time ?? '-'} menit
                                </p>
                            </div>
                            <div className="rounded-lg border p-3 text-center">
                                <p className="text-muted-foreground">
                                    Rata-rata Layanan
                                </p>
                                <p className="text-2xl font-bold">
                                    {statistics.average_service_time ?? '-'}{' '}
                                    menit
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
