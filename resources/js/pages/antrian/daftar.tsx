import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle,
    Clock,
    FileText,
    Users,
    X,
} from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

interface DaftarProps {
    services: ServiceOption[];
}

export default function Daftar({ services }: DaftarProps) {
    const [selectedService, setSelectedService] = useState<number | null>(null);
    const [name, setName] = useState('');
    const [nik, setNik] = useState('');
    const [phone, setPhone] = useState('');
    const [email, setEmail] = useState('');
    const [notifyEmail, setNotifyEmail] = useState(false);
    const [notifySms, setNotifySms] = useState(false);
    const [isPriority, setIsPriority] = useState(false);
    const [documents, setDocuments] = useState<File[]>([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const selectedServiceData = services.find((s) => s.id === selectedService);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files || []);
        const validFiles = files.filter(
            (file) =>
                file.size <= 5 * 1024 * 1024 &&
                [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'image/jpg',
                ].includes(file.type),
        );

        if (validFiles.length !== files.length) {
            setErrors({
                ...errors,
                documents:
                    'Beberapa file tidak valid. Maksimal 5MB, format PDF/JPG/PNG.',
            });
        } else {
            setErrors({ ...errors, documents: '' });
        }

        setDocuments([...documents, ...validFiles]);
        e.target.value = '';
    };

    const removeDocument = (index: number) => {
        setDocuments(documents.filter((_, i) => i !== index));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!selectedService) {
            setErrors({ service_id: 'Silakan pilih layanan' });
            return;
        }

        if (!name.trim()) {
            setErrors({ name: 'Nama wajib diisi' });
            return;
        }

        setIsSubmitting(true);
        setErrors({});

        const formData = new FormData();
        formData.append('service_id', selectedService.toString());
        formData.append('name', name.trim());
        if (nik.trim()) {
            formData.append('nik', nik.trim());
        }
        if (phone.trim()) {
            formData.append('phone', phone.trim());
        }
        if (email.trim()) {
            formData.append('email', email.trim());
        }
        formData.append('notify_email', notifyEmail ? '1' : '0');
        formData.append('notify_sms', notifySms ? '1' : '0');
        formData.append('is_priority', isPriority ? '1' : '0');

        documents.forEach((file, index) => {
            formData.append(`documents[${index}]`, file);
        });

        router.post('/antrian/daftar', formData, {
            onError: (errs: Record<string, string>) => {
                setErrors(errs);
                setIsSubmitting(false);
            },
            onFinish: () => setIsSubmitting(false),
        });
    };

    return (
        <>
            <Head title="Daftar Antrian" />

            <div className="flex min-h-screen flex-col bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
                <header className="bg-primary p-6 text-white shadow-lg">
                    <div className="mx-auto max-w-4xl text-center">
                        <h1 className="text-2xl font-bold">
                            Pengadilan Agama Penajam
                        </h1>
                        <p className="text-primary-foreground/80">
                            Sistem Antrian Layanan PTSP
                        </p>
                    </div>
                </header>

                <main className="mx-auto w-full max-w-4xl flex-1 p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Pilih Layanan</CardTitle>
                                <CardDescription>
                                    Pilih jenis layanan yang Anda butuhkan
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-3">
                                    {services.map((service) => (
                                        <button
                                            key={service.id}
                                            type="button"
                                            disabled={!service.is_available}
                                            onClick={() =>
                                                setSelectedService(service.id)
                                            }
                                            className={cn(
                                                'relative flex flex-col items-center rounded-lg border-2 p-4 text-center transition-all',
                                                selectedService === service.id
                                                    ? 'border-primary bg-primary/5 ring-2 ring-primary'
                                                    : 'border-muted hover:border-primary/50',
                                                !service.is_available &&
                                                    'cursor-not-allowed opacity-50',
                                            )}
                                        >
                                            {selectedService === service.id && (
                                                <CheckCircle className="absolute top-2 right-2 size-5 text-primary" />
                                            )}
                                            <span className="text-lg font-bold">
                                                {service.name}
                                            </span>
                                            {service.description && (
                                                <span className="mt-1 text-xs text-muted-foreground">
                                                    {service.description}
                                                </span>
                                            )}
                                            <div className="mt-2 flex items-center gap-2 text-xs text-muted-foreground">
                                                <Clock className="size-3" />
                                                <span>
                                                    ± {service.average_time}{' '}
                                                    menit
                                                </span>
                                            </div>
                                            <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                                <Users className="size-3" />
                                                <span>
                                                    {service.today_queue_count}/
                                                    {service.max_daily_queue}
                                                </span>
                                            </div>
                                            {!service.is_available && (
                                                <Badge
                                                    variant="destructive"
                                                    className="mt-2"
                                                >
                                                    Tidak Tersedia
                                                </Badge>
                                            )}
                                        </button>
                                    ))}
                                </div>
                                {errors.service_id && (
                                    <p className="mt-2 flex items-center gap-1 text-sm text-red-500">
                                        <AlertCircle className="size-4" />
                                        {errors.service_id}
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Data Pengunjung</CardTitle>
                                <CardDescription>
                                    Isi data diri Anda
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">
                                        Nama Lengkap{' '}
                                        <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        value={name}
                                        onChange={(e) =>
                                            setName(e.target.value)
                                        }
                                        placeholder="Masukkan nama lengkap"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="flex items-center gap-1 text-sm text-red-500">
                                            <AlertCircle className="size-4" />
                                            {errors.name}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="nik">
                                            NIK (opsional)
                                        </Label>
                                        <Input
                                            id="nik"
                                            value={nik}
                                            onChange={(e) =>
                                                setNik(e.target.value)
                                            }
                                            placeholder="16 digit NIK"
                                            maxLength={16}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="phone">
                                            No. Telepon (opsional)
                                        </Label>
                                        <Input
                                            id="phone"
                                            type="tel"
                                            value={phone}
                                            onChange={(e) =>
                                                setPhone(e.target.value)
                                            }
                                            placeholder="08xxxxxxxxxx"
                                        />
                                        {phone.trim() && (
                                            <div className="flex items-start space-x-2 rounded-lg border p-3">
                                                <Checkbox
                                                    id="notifySms"
                                                    checked={notifySms}
                                                    onCheckedChange={(
                                                        checked,
                                                    ) =>
                                                        setNotifySms(
                                                            checked === true,
                                                        )
                                                    }
                                                />
                                                <Label
                                                    htmlFor="notifySms"
                                                    className="cursor-pointer text-xs leading-snug"
                                                >
                                                    Kirim notifikasi WhatsApp
                                                    saat giliran hampir tiba
                                                </Label>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">
                                        Email (opsional)
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={email}
                                        onChange={(e) =>
                                            setEmail(e.target.value)
                                        }
                                        placeholder="nama@email.com"
                                    />
                                    {errors.email && (
                                        <p className="flex items-center gap-1 text-sm text-red-500">
                                            <AlertCircle className="size-4" />
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                {email.trim() && (
                                    <div className="flex items-center space-x-2 rounded-lg border p-4">
                                        <Checkbox
                                            id="notifyEmail"
                                            checked={notifyEmail}
                                            onCheckedChange={(checked) =>
                                                setNotifyEmail(checked === true)
                                            }
                                        />
                                        <Label
                                            htmlFor="notifyEmail"
                                            className="cursor-pointer text-sm font-normal"
                                        >
                                            Kirim notifikasi email saat giliran
                                            hampir tiba
                                        </Label>
                                    </div>
                                )}

                                <div className="flex items-center space-x-2 rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-950/20">
                                    <Checkbox
                                        id="priority"
                                        checked={isPriority}
                                        onCheckedChange={(checked) =>
                                            setIsPriority(checked === true)
                                        }
                                    />
                                    <Label
                                        htmlFor="priority"
                                        className="cursor-pointer text-sm"
                                    >
                                        Saya termasuk prioritas (lansia 60+
                                        tahun, ibu hamil, atau penyandang
                                        disabilitas)
                                    </Label>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Dokumen Pendukung</CardTitle>
                                <CardDescription>
                                    Upload dokumen yang diperlukan (Maks. 5MB
                                    per file)
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="documents">
                                        Upload File
                                    </Label>
                                    <Input
                                        id="documents"
                                        type="file"
                                        multiple
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        onChange={handleFileChange}
                                        className="cursor-pointer"
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Format: PDF, JPG, PNG.
                                    </p>
                                </div>

                                {documents.length > 0 && (
                                    <div className="space-y-2">
                                        <Label>File Terpilih:</Label>
                                        <div className="grid gap-2">
                                            {documents.map((file, index) => (
                                                <div
                                                    key={index}
                                                    className="flex items-center justify-between rounded-md border p-2 text-sm"
                                                >
                                                    <div className="flex items-center gap-2 truncate">
                                                        <FileText className="size-4 text-blue-500" />
                                                        <span className="truncate">
                                                            {file.name}
                                                        </span>
                                                        <span className="text-xs text-muted-foreground">
                                                            (
                                                            {(
                                                                file.size /
                                                                1024 /
                                                                1024
                                                            ).toFixed(2)}{' '}
                                                            MB)
                                                        </span>
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className="h-6 w-6 text-red-500 hover:bg-red-50 hover:text-red-600"
                                                        onClick={() =>
                                                            removeDocument(
                                                                index,
                                                            )
                                                        }
                                                    >
                                                        <X className="size-4" />
                                                    </Button>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {errors.documents && (
                                    <p className="flex items-center gap-1 text-sm text-red-500">
                                        <AlertCircle className="size-4" />
                                        {errors.documents}
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {selectedServiceData && (
                            <Card className="border-primary/20 bg-primary/5">
                                <CardContent className="pt-6">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Layanan dipilih:
                                            </p>
                                            <p className="text-lg font-bold">
                                                {selectedServiceData.name}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm text-muted-foreground">
                                                Estimasi waktu:
                                            </p>
                                            <p className="text-lg font-bold">
                                                ±{' '}
                                                {
                                                    selectedServiceData.average_time
                                                }{' '}
                                                menit
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        <Button
                            type="submit"
                            size="lg"
                            className="w-full"
                            disabled={
                                isSubmitting || !selectedService || !name.trim()
                            }
                        >
                            {isSubmitting
                                ? 'Memproses...'
                                : 'Ambil Nomor Antrian'}
                        </Button>
                    </form>
                </main>

                <footer className="border-t p-4 text-center">
                    <p className="text-sm text-muted-foreground">
                        © 2025 Pengadilan Agama Penajam - Sistem Antrian PTSP
                    </p>
                </footer>
            </div>
        </>
    );
}
