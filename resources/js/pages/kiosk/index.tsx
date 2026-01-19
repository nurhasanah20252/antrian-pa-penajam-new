import { Head, router } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Clock, Users } from 'lucide-react';
import { useEffect, useState } from 'react';

import { cn } from '@/lib/utils';

interface ServiceOption {
    id: number;
    code: string;
    name: string;
    description: string | null;
    average_time: number;
    is_available: boolean;
    today_queue_count: number;
    max_daily_queue: number;
}

interface KioskProps {
    services: ServiceOption[];
}

type Step = 'service' | 'name' | 'priority' | 'confirm';

const INACTIVITY_TIMEOUT_MS = 120000;
const CLOCK_UPDATE_INTERVAL_MS = 1000;

export default function KioskIndex({ services }: KioskProps) {
    const [step, setStep] = useState<Step>('service');
    const [selectedService, setSelectedService] = useState<number | null>(null);
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [notifyEmail, setNotifyEmail] = useState(false);
    const [isPriority, setIsPriority] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [currentTime, setCurrentTime] = useState(new Date());

    const selectedServiceData = services.find((s) => s.id === selectedService);

    useEffect(() => {
        const timer = setInterval(
            () => setCurrentTime(new Date()),
            CLOCK_UPDATE_INTERVAL_MS,
        );
        return () => clearInterval(timer);
    }, []);

    useEffect(() => {
        const timeout = setTimeout(() => {
            if (step !== 'service') {
                handleReset();
            }
        }, INACTIVITY_TIMEOUT_MS);
        return () => clearTimeout(timeout);
    }, [step, name, email, notifyEmail, selectedService, isPriority]);

    const handleReset = () => {
        setStep('service');
        setSelectedService(null);
        setName('');
        setEmail('');
        setNotifyEmail(false);
        setIsPriority(false);
        setErrors({});
        setIsSubmitting(false);
    };

    const handleSelectService = (serviceId: number) => {
        setSelectedService(serviceId);
        setStep('name');
    };

    const handleNameSubmit = () => {
        if (!name.trim()) {
            setErrors({ name: 'Nama wajib diisi' });
            return;
        }
        setErrors({});
        setStep('priority');
    };

    const handlePrioritySelect = (priority: boolean) => {
        setIsPriority(priority);
        setStep('confirm');
    };

    const handleSubmit = () => {
        if (!selectedService || !name.trim()) return;

        setIsSubmitting(true);
        setErrors({});

        router.post(
            '/kiosk',
            {
                service_id: selectedService,
                name: name.trim(),
                email: email.trim() || null,
                notify_email: notifyEmail,
                is_priority: isPriority,
            },
            {
                onError: (errs) => {
                    setErrors(errs as Record<string, string>);
                    setIsSubmitting(false);
                },
                onFinish: () => setIsSubmitting(false),
            },
        );
    };

    return (
        <>
            <Head title="Kiosk - Ambil Antrian" />

            <div className="flex h-screen flex-col bg-gradient-to-br from-slate-900 to-slate-800">
                <header className="flex items-center justify-between bg-primary px-8 py-4 text-white">
                    <div>
                        <h1 className="text-3xl font-bold">
                            Pengadilan Agama Penajam
                        </h1>
                        <p className="text-lg text-primary-foreground/80">
                            Sistem Antrian Layanan PTSP
                        </p>
                    </div>
                    <div className="text-right">
                        <p className="text-4xl font-bold tabular-nums">
                            {currentTime.toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit',
                            })}
                        </p>
                        <p className="text-lg text-primary-foreground/80">
                            {currentTime.toLocaleDateString('id-ID', {
                                weekday: 'long',
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                            })}
                        </p>
                    </div>
                </header>

                <main className="flex flex-1 flex-col items-center justify-center p-8">
                    {step === 'service' && (
                        <div className="w-full max-w-5xl">
                            <h2 className="mb-8 text-center text-4xl font-bold text-white">
                                Pilih Layanan
                            </h2>
                            <div className="grid grid-cols-3 gap-6">
                                {services.map((service) => (
                                    <button
                                        key={service.id}
                                        disabled={!service.is_available}
                                        onClick={() =>
                                            handleSelectService(service.id)
                                        }
                                        className={cn(
                                            'relative flex min-h-[200px] flex-col items-center justify-center rounded-2xl border-4 p-8 text-center transition-all duration-200',
                                            service.is_available
                                                ? 'border-white/20 bg-white/10 text-white hover:scale-105 hover:border-primary hover:bg-white/20 active:scale-95'
                                                : 'cursor-not-allowed border-white/5 bg-white/5 text-white/30',
                                        )}
                                    >
                                        <span className="text-3xl font-bold">
                                            {service.name}
                                        </span>
                                        {service.description && (
                                            <span className="mt-2 text-lg opacity-70">
                                                {service.description}
                                            </span>
                                        )}
                                        <div className="mt-4 flex items-center gap-4 text-lg opacity-70">
                                            <div className="flex items-center gap-2">
                                                <Clock className="size-5" />
                                                <span>
                                                    ± {service.average_time}{' '}
                                                    menit
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Users className="size-5" />
                                                <span>
                                                    {service.today_queue_count}/
                                                    {service.max_daily_queue}
                                                </span>
                                            </div>
                                        </div>
                                        {!service.is_available && (
                                            <span className="mt-4 rounded-full bg-red-500/20 px-4 py-2 text-lg font-medium text-red-400">
                                                Tidak Tersedia
                                            </span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {step === 'name' && (
                        <div className="w-full max-w-2xl text-center">
                            <h2 className="mb-2 text-4xl font-bold text-white">
                                Masukkan Nama Anda
                            </h2>
                            <p className="mb-8 text-xl text-white/60">
                                Layanan: {selectedServiceData?.name}
                            </p>
                            <div className="space-y-6">
                                <input
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    placeholder="Ketik nama lengkap Anda"
                                    autoFocus
                                    className="w-full rounded-2xl border-4 border-white/20 bg-white/10 px-8 py-6 text-center text-3xl font-medium text-white placeholder:text-white/30 focus:border-primary focus:outline-none"
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            handleNameSubmit();
                                        }
                                    }}
                                />
                                {errors.name && (
                                    <p className="flex items-center justify-center gap-2 text-xl text-red-400">
                                        <AlertCircle className="size-6" />
                                        {errors.name}
                                    </p>
                                )}

                                <div className="space-y-4">
                                    <input
                                        type="email"
                                        value={email}
                                        onChange={(e) =>
                                            setEmail(e.target.value)
                                        }
                                        placeholder="Email (opsional)"
                                        className="w-full rounded-2xl border-4 border-white/20 bg-white/10 px-8 py-4 text-center text-2xl font-medium text-white placeholder:text-white/30 focus:border-primary focus:outline-none"
                                    />
                                    {errors.email && (
                                        <p className="flex items-center justify-center gap-2 text-lg text-red-400">
                                            <AlertCircle className="size-5" />
                                            {errors.email}
                                        </p>
                                    )}

                                    {email.trim() && (
                                        <div
                                            className="mx-auto flex w-fit cursor-pointer items-center gap-4 rounded-xl border-2 border-white/20 bg-white/5 px-6 py-4 transition-all hover:bg-white/10"
                                            onClick={() =>
                                                setNotifyEmail(!notifyEmail)
                                            }
                                        >
                                            <div
                                                className={cn(
                                                    'flex size-8 items-center justify-center rounded-lg border-2 border-white transition-all',
                                                    notifyEmail
                                                        ? 'border-primary bg-primary'
                                                        : 'bg-transparent',
                                                )}
                                            >
                                                {notifyEmail && (
                                                    <CheckCircle className="size-6 text-white" />
                                                )}
                                            </div>
                                            <span className="text-xl text-white">
                                                Kirim notifikasi email
                                            </span>
                                        </div>
                                    )}
                                </div>

                                <div className="flex gap-4">
                                    <button
                                        onClick={() => setStep('service')}
                                        className="flex-1 rounded-2xl border-4 border-white/20 bg-white/10 py-6 text-2xl font-bold text-white transition-all hover:bg-white/20 active:scale-95"
                                    >
                                        Kembali
                                    </button>
                                    <button
                                        onClick={handleNameSubmit}
                                        disabled={!name.trim()}
                                        className="flex-1 rounded-2xl bg-primary py-6 text-2xl font-bold text-white transition-all hover:bg-primary/90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Lanjut
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 'priority' && (
                        <div className="w-full max-w-3xl text-center">
                            <h2 className="mb-2 text-4xl font-bold text-white">
                                Apakah Anda Termasuk Prioritas?
                            </h2>
                            <p className="mb-8 text-xl text-white/60">
                                Lansia (60+ tahun), Ibu Hamil, atau Penyandang
                                Disabilitas
                            </p>
                            <div className="grid grid-cols-2 gap-6">
                                <button
                                    onClick={() => handlePrioritySelect(true)}
                                    className="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border-4 border-orange-500/50 bg-orange-500/20 p-8 text-white transition-all hover:scale-105 hover:border-orange-500 hover:bg-orange-500/30 active:scale-95"
                                >
                                    <CheckCircle className="mb-4 size-16 text-orange-400" />
                                    <span className="text-3xl font-bold">
                                        Ya, Saya Prioritas
                                    </span>
                                </button>
                                <button
                                    onClick={() => handlePrioritySelect(false)}
                                    className="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border-4 border-white/20 bg-white/10 p-8 text-white transition-all hover:scale-105 hover:border-white/50 hover:bg-white/20 active:scale-95"
                                >
                                    <span className="mb-4 text-6xl">👤</span>
                                    <span className="text-3xl font-bold">
                                        Tidak
                                    </span>
                                </button>
                            </div>
                            <button
                                onClick={() => setStep('name')}
                                className="mt-6 rounded-2xl border-4 border-white/20 bg-white/10 px-12 py-4 text-xl font-bold text-white transition-all hover:bg-white/20 active:scale-95"
                            >
                                Kembali
                            </button>
                        </div>
                    )}

                    {step === 'confirm' && (
                        <div className="w-full max-w-2xl text-center">
                            <h2 className="mb-8 text-4xl font-bold text-white">
                                Konfirmasi Data
                            </h2>
                            <div className="mb-8 space-y-4 rounded-2xl border-4 border-white/20 bg-white/10 p-8 text-left text-white">
                                <div className="flex justify-between border-b border-white/10 pb-4 text-2xl">
                                    <span className="opacity-70">Layanan:</span>
                                    <span className="font-bold">
                                        {selectedServiceData?.name}
                                    </span>
                                </div>
                                <div className="flex justify-between border-b border-white/10 pb-4 text-2xl">
                                    <span className="opacity-70">Nama:</span>
                                    <span className="font-bold">{name}</span>
                                </div>
                                <div className="flex justify-between text-2xl">
                                    <span className="opacity-70">Status:</span>
                                    <span className="font-bold">
                                        {isPriority ? (
                                            <span className="text-orange-400">
                                                Prioritas
                                            </span>
                                        ) : (
                                            'Reguler'
                                        )}
                                    </span>
                                </div>
                            </div>
                            {(errors.service_id || errors.name) && (
                                <p className="mb-4 flex items-center justify-center gap-2 text-xl text-red-400">
                                    <AlertCircle className="size-6" />
                                    {errors.service_id || errors.name}
                                </p>
                            )}
                            <div className="flex gap-4">
                                <button
                                    onClick={() => setStep('priority')}
                                    disabled={isSubmitting}
                                    className="flex-1 rounded-2xl border-4 border-white/20 bg-white/10 py-6 text-2xl font-bold text-white transition-all hover:bg-white/20 active:scale-95 disabled:opacity-50"
                                >
                                    Kembali
                                </button>
                                <button
                                    onClick={handleSubmit}
                                    disabled={isSubmitting}
                                    className="flex-1 rounded-2xl bg-green-600 py-6 text-2xl font-bold text-white transition-all hover:bg-green-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {isSubmitting
                                        ? 'Memproses...'
                                        : 'Ambil Nomor Antrian'}
                                </button>
                            </div>
                        </div>
                    )}
                </main>

                <footer className="border-t border-white/10 px-8 py-4 text-center text-white/40">
                    <p>
                        Tekan layar untuk memulai • Auto-reset setelah 2 menit
                        tidak aktif
                    </p>
                </footer>

                {step !== 'service' && (
                    <button
                        onClick={handleReset}
                        className="fixed right-8 bottom-24 rounded-full bg-red-600 px-6 py-3 text-lg font-bold text-white shadow-lg transition-all hover:bg-red-500 active:scale-95"
                    >
                        Batalkan
                    </button>
                )}
            </div>
        </>
    );
}
