import { Head, Link, usePoll } from '@inertiajs/react';
import { Clock, Printer, QrCode, Users } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { Queue } from '@/types';

interface TiketProps {
    queue: Queue;
    position: number;
    estimated_wait: number;
}

export default function Tiket({ queue, position, estimated_wait }: TiketProps) {
    usePoll(10000);

    const handlePrint = () => {
        window.print();
    };

    return (
        <>
            <Head title={`Tiket ${queue.number}`} />

            <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 p-6 dark:from-slate-900 dark:to-slate-800">
                <Card className="w-full max-w-md overflow-hidden shadow-xl print:shadow-none">
                    <div className="bg-primary p-6 text-center text-white">
                        <h1 className="text-xl font-bold">
                            Pengadilan Agama Penajam
                        </h1>
                        <p className="text-sm text-primary-foreground/80">
                            Layanan PTSP
                        </p>
                    </div>

                    <CardContent className="space-y-6 p-6">
                        <div className="text-center">
                            <p className="text-sm tracking-wide text-muted-foreground uppercase">
                                Nomor Antrian Anda
                            </p>
                            <p className="text-6xl font-black tracking-tight text-primary">
                                {queue.number}
                            </p>
                            {queue.is_priority && (
                                <Badge variant="destructive" className="mt-2">
                                    Prioritas
                                </Badge>
                            )}
                        </div>

                        <div className="space-y-3 rounded-lg bg-muted/50 p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">
                                    Layanan
                                </span>
                                <span className="font-medium">
                                    {queue.service?.name}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">
                                    Nama
                                </span>
                                <span className="font-medium">
                                    {queue.name}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">
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
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-muted-foreground">
                                    Jam
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
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="rounded-lg border p-3 text-center">
                                <Users className="mx-auto mb-1 size-5 text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">
                                    Posisi Antrian
                                </p>
                                <p className="text-xl font-bold">{position}</p>
                            </div>
                            <div className="rounded-lg border p-3 text-center">
                                <Clock className="mx-auto mb-1 size-5 text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">
                                    Estimasi Tunggu
                                </p>
                                <p className="text-xl font-bold">
                                    ± {estimated_wait} menit
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center justify-center rounded-lg border-2 border-dashed p-4">
                            <div className="text-center">
                                <QrCode className="mx-auto size-24 text-muted-foreground" />
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Scan untuk cek status
                                </p>
                            </div>
                        </div>

                        <div className="text-center text-xs text-muted-foreground">
                            <p>
                                Simpan tiket ini dan tunggu nomor Anda
                                dipanggil.
                            </p>
                            <p>
                                Anda dapat cek status antrian di display atau
                                melalui website.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div className="mt-6 flex gap-4 print:hidden">
                    <Button onClick={handlePrint} variant="outline">
                        <Printer className="mr-2 size-4" />
                        Cetak Tiket
                    </Button>
                    <Button asChild>
                        <Link href={`/antrian/status/${queue.number}`}>
                            Cek Status
                        </Link>
                    </Button>
                </div>

                <div className="mt-4 print:hidden">
                    <Link
                        href="/antrian/daftar"
                        className="text-sm text-muted-foreground underline hover:text-primary"
                    >
                        Ambil antrian baru
                    </Link>
                </div>
            </div>
        </>
    );
}
