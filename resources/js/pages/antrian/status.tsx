import { Head, Link, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle,
    Clock,
    Megaphone,
    Search,
    User,
    Users,
} from 'lucide-react';
import { useState } from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import type { Queue, QueueStatus } from '@/types';

interface StatusProps {
    queue?: Queue;
    position?: number;
    searched_number?: string;
    error?: string;
}

const statusConfig: Record<
    QueueStatus,
    { label: string; color: string; icon: React.ElementType }
> = {
    waiting: {
        label: 'Menunggu',
        color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        icon: Clock,
    },
    called: {
        label: 'Dipanggil',
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        icon: Megaphone,
    },
    processing: {
        label: 'Diproses',
        color: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        icon: User,
    },
    completed: {
        label: 'Selesai',
        color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        icon: CheckCircle,
    },
    skipped: {
        label: 'Dilewati',
        color: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        icon: AlertCircle,
    },
    cancelled: {
        label: 'Dibatalkan',
        color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        icon: AlertCircle,
    },
};

export default function Status({
    queue,
    position,
    searched_number,
    error,
}: StatusProps) {
    const [number, setNumber] = useState(searched_number ?? '');
    const [isSearching, setIsSearching] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (!number.trim()) {
            return;
        }
        setIsSearching(true);
        router.get(
            `/antrian/status/${number.trim().toUpperCase()}`,
            {},
            {
                preserveState: true,
                onFinish: () => setIsSearching(false),
            },
        );
    };

    const StatusIcon = queue ? statusConfig[queue.status].icon : Clock;

    return (
        <>
            <Head title="Cek Status Antrian" />

            <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 p-6 dark:from-slate-900 dark:to-slate-800">
                <div className="w-full max-w-md space-y-6">
                    <div className="text-center">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Cek Status Antrian
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Masukkan nomor antrian Anda
                        </p>
                    </div>

                    <Card>
                        <CardContent className="pt-6">
                            <form onSubmit={handleSearch} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="number">
                                        Nomor Antrian
                                    </Label>
                                    <div className="flex gap-2">
                                        <Input
                                            id="number"
                                            type="text"
                                            placeholder="Contoh: A001"
                                            value={number}
                                            onChange={(e) =>
                                                setNumber(
                                                    e.target.value.toUpperCase(),
                                                )
                                            }
                                            className="text-center font-mono text-lg tracking-widest uppercase"
                                            maxLength={10}
                                        />
                                        <Button
                                            type="submit"
                                            disabled={
                                                isSearching || !number.trim()
                                            }
                                        >
                                            <Search className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {error && (
                        <Alert variant="destructive">
                            <AlertCircle className="size-4" />
                            <AlertTitle>Tidak Ditemukan</AlertTitle>
                            <AlertDescription>{error}</AlertDescription>
                        </Alert>
                    )}

                    {queue && (
                        <Card className="overflow-hidden">
                            <div className="bg-primary p-4 text-center text-white">
                                <p className="text-sm text-primary-foreground/80">
                                    Nomor Antrian
                                </p>
                                <p className="text-4xl font-black tracking-tight">
                                    {queue.number}
                                </p>
                            </div>

                            <CardHeader className="pb-2">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg">
                                        {queue.service?.name}
                                    </CardTitle>
                                    <Badge
                                        className={
                                            statusConfig[queue.status].color
                                        }
                                    >
                                        <StatusIcon className="mr-1 size-3" />
                                        {statusConfig[queue.status].label}
                                    </Badge>
                                </div>
                                <CardDescription>
                                    {queue.name}{' '}
                                    {queue.is_priority && '• Prioritas'}
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="space-y-4">
                                {queue.status === 'waiting' &&
                                    position !== undefined &&
                                    position > 0 && (
                                        <div className="rounded-lg bg-yellow-50 p-4 text-center dark:bg-yellow-900/20">
                                            <Users className="mx-auto mb-2 size-8 text-yellow-600 dark:text-yellow-400" />
                                            <p className="text-sm text-yellow-800 dark:text-yellow-300">
                                                Anda berada di posisi
                                            </p>
                                            <p className="text-3xl font-bold text-yellow-900 dark:text-yellow-200">
                                                {position}
                                            </p>
                                            <p className="mt-1 text-xs text-yellow-700 dark:text-yellow-400">
                                                Estimasi ±{' '}
                                                {position *
                                                    (queue.service
                                                        ?.average_time ??
                                                        5)}{' '}
                                                menit
                                            </p>
                                        </div>
                                    )}

                                {queue.status === 'called' && (
                                    <div className="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                                        <Megaphone className="mx-auto mb-2 size-8 text-blue-600 dark:text-blue-400" />
                                        <p className="text-sm text-blue-800 dark:text-blue-300">
                                            Nomor Anda sedang dipanggil!
                                        </p>
                                        {queue.officer && (
                                            <p className="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-200">
                                                Loket{' '}
                                                {queue.officer.counter_number}
                                            </p>
                                        )}
                                        <p className="mt-1 text-xs text-blue-700 dark:text-blue-400">
                                            Silakan menuju loket yang tertera
                                        </p>
                                    </div>
                                )}

                                {queue.status === 'processing' && (
                                    <div className="rounded-lg bg-purple-50 p-4 text-center dark:bg-purple-900/20">
                                        <User className="mx-auto mb-2 size-8 text-purple-600 dark:text-purple-400" />
                                        <p className="text-sm text-purple-800 dark:text-purple-300">
                                            Antrian Anda sedang dilayani
                                        </p>
                                        {queue.officer && (
                                            <p className="mt-2 text-lg font-semibold text-purple-900 dark:text-purple-200">
                                                Loket{' '}
                                                {queue.officer.counter_number}
                                            </p>
                                        )}
                                    </div>
                                )}

                                {queue.status === 'completed' && (
                                    <div className="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                                        <CheckCircle className="mx-auto mb-2 size-8 text-green-600 dark:text-green-400" />
                                        <p className="text-sm text-green-800 dark:text-green-300">
                                            Layanan telah selesai
                                        </p>
                                        <p className="mt-1 text-xs text-green-700 dark:text-green-400">
                                            Terima kasih telah menggunakan
                                            layanan kami
                                        </p>
                                    </div>
                                )}

                                {queue.status === 'skipped' && (
                                    <div className="rounded-lg bg-orange-50 p-4 text-center dark:bg-orange-900/20">
                                        <AlertCircle className="mx-auto mb-2 size-8 text-orange-600 dark:text-orange-400" />
                                        <p className="text-sm text-orange-800 dark:text-orange-300">
                                            Antrian Anda dilewati
                                        </p>
                                        <p className="mt-1 text-xs text-orange-700 dark:text-orange-400">
                                            Silakan hubungi petugas untuk
                                            informasi lebih lanjut
                                        </p>
                                    </div>
                                )}

                                {queue.status === 'cancelled' && (
                                    <div className="rounded-lg bg-red-50 p-4 text-center dark:bg-red-900/20">
                                        <AlertCircle className="mx-auto mb-2 size-8 text-red-600 dark:text-red-400" />
                                        <p className="text-sm text-red-800 dark:text-red-300">
                                            Antrian dibatalkan
                                        </p>
                                    </div>
                                )}

                                <div className="space-y-2 rounded-lg border p-4 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Tanggal
                                        </span>
                                        <span className="font-medium">
                                            {new Date(
                                                queue.created_at,
                                            ).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Waktu Daftar
                                        </span>
                                        <span className="font-medium">
                                            {new Date(
                                                queue.created_at,
                                            ).toLocaleTimeString('id-ID', {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </span>
                                    </div>
                                    {queue.called_at && (
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Waktu Panggil
                                            </span>
                                            <span className="font-medium">
                                                {new Date(
                                                    queue.called_at,
                                                ).toLocaleTimeString('id-ID', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </span>
                                        </div>
                                    )}
                                    {queue.completed_at && (
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Waktu Selesai
                                            </span>
                                            <span className="font-medium">
                                                {new Date(
                                                    queue.completed_at,
                                                ).toLocaleTimeString('id-ID', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}
                                            </span>
                                        </div>
                                    )}
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Sumber
                                        </span>
                                        <span className="font-medium capitalize">
                                            {queue.source}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <div className="flex justify-center gap-4">
                        <Button variant="outline" asChild>
                            <Link href="/antrian/daftar">
                                <ArrowLeft className="mr-2 size-4" />
                                Ambil Antrian
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/display">Display Board</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
