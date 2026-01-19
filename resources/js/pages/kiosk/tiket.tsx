import { Head, router } from '@inertiajs/react';
import { Clock, Printer, Users } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Badge } from '@/components/ui/badge';
import type { Queue } from '@/types';

interface KioskTiketProps {
    queue: Queue;
    position: number;
    estimated_wait: number;
}

const AUTO_REDIRECT_DELAY_MS = 15000;

export default function KioskTiket({
    queue,
    position,
    estimated_wait,
}: KioskTiketProps) {
    const [countdown, setCountdown] = useState(AUTO_REDIRECT_DELAY_MS / 1000);

    useEffect(() => {
        const timer = setInterval(() => {
            setCountdown((prev) => {
                if (prev <= 1) {
                    router.visit('/kiosk');
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    const handlePrint = () => {
        window.print();
    };

    const handleNewQueue = () => {
        router.visit('/kiosk');
    };

    return (
        <>
            <Head title={`Tiket ${queue.number}`} />

            <div className="flex h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-900 to-slate-800 p-8 print:bg-white">
                <div className="w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl print:shadow-none">
                    <div className="bg-primary p-8 text-center text-white print:bg-green-600">
                        <h1 className="text-3xl font-bold">
                            Pengadilan Agama Penajam
                        </h1>
                        <p className="text-xl text-primary-foreground/80">
                            Layanan PTSP
                        </p>
                    </div>

                    <div className="space-y-8 p-8">
                        <div className="text-center">
                            <p className="text-xl tracking-wide text-gray-500 uppercase">
                                Nomor Antrian Anda
                            </p>
                            <p className="text-8xl font-black tracking-tight text-primary">
                                {queue.number}
                            </p>
                            {queue.is_priority && (
                                <Badge
                                    variant="destructive"
                                    className="mt-4 px-6 py-2 text-lg"
                                >
                                    PRIORITAS
                                </Badge>
                            )}
                        </div>

                        <div className="space-y-4 rounded-2xl bg-gray-100 p-6 text-xl">
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500">Layanan</span>
                                <span className="font-bold">
                                    {queue.service?.name}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500">Nama</span>
                                <span className="font-bold">{queue.name}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500">Tanggal</span>
                                <span className="font-bold">
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
                                <span className="text-gray-500">Jam</span>
                                <span className="font-bold">
                                    {new Date(
                                        queue.created_at,
                                    ).toLocaleTimeString('id-ID', {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    })}
                                </span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-6">
                            <div className="rounded-2xl border-2 border-gray-200 p-6 text-center">
                                <Users className="mx-auto mb-2 size-10 text-gray-400" />
                                <p className="text-lg text-gray-500">
                                    Posisi Antrian
                                </p>
                                <p className="text-4xl font-bold text-gray-900">
                                    {position}
                                </p>
                            </div>
                            <div className="rounded-2xl border-2 border-gray-200 p-6 text-center">
                                <Clock className="mx-auto mb-2 size-10 text-gray-400" />
                                <p className="text-lg text-gray-500">
                                    Estimasi Tunggu
                                </p>
                                <p className="text-4xl font-bold text-gray-900">
                                    ± {estimated_wait} menit
                                </p>
                            </div>
                        </div>

                        <div className="text-center text-lg text-gray-500">
                            <p>
                                Simpan tiket ini dan tunggu nomor Anda
                                dipanggil.
                            </p>
                            <p>Perhatikan display panggilan di ruang tunggu.</p>
                        </div>
                    </div>
                </div>

                <div className="mt-8 flex gap-6 print:hidden">
                    <button
                        onClick={handlePrint}
                        className="flex items-center gap-3 rounded-2xl border-4 border-white/20 bg-white/10 px-8 py-4 text-xl font-bold text-white transition-all hover:bg-white/20 active:scale-95"
                    >
                        <Printer className="size-6" />
                        Cetak Tiket
                    </button>
                    <button
                        onClick={handleNewQueue}
                        className="rounded-2xl bg-primary px-8 py-4 text-xl font-bold text-white transition-all hover:bg-primary/90 active:scale-95"
                    >
                        Ambil Antrian Baru
                    </button>
                </div>

                <p className="mt-6 text-lg text-white/50 print:hidden">
                    Kembali ke halaman utama dalam {countdown} detik...
                </p>
            </div>
        </>
    );
}
