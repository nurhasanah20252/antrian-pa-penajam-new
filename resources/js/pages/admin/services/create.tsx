import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEvent, useState } from 'react';

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
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Layanan', href: '/admin/services' },
    { title: 'Tambah', href: '/admin/services/create' },
];

export default function ServicesCreate() {
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setProcessing(true);

        const formData = new FormData(e.currentTarget);
        const data = {
            code: formData.get('code') as string,
            name: formData.get('name') as string,
            description: formData.get('description') as string,
            prefix: formData.get('prefix') as string,
            average_time:
                parseInt(formData.get('average_time') as string) || 15,
            max_daily_queue:
                parseInt(formData.get('max_daily_queue') as string) || 100,
            is_active: formData.get('is_active') === 'on',
            requires_documents: formData.get('requires_documents') === 'on',
            sort_order: parseInt(formData.get('sort_order') as string) || 0,
        };

        router.post('/admin/services', data, {
            onError: (errs) => {
                setErrors(errs);
                setProcessing(false);
            },
            onSuccess: () => setProcessing(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tambah Layanan" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/services">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Tambah Layanan</h1>
                        <p className="text-muted-foreground">
                            Buat layanan baru untuk antrian PTSP
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Layanan</CardTitle>
                            <CardDescription>
                                Isi detail layanan yang akan dibuat
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="code">Kode Layanan *</Label>
                                    <Input
                                        id="code"
                                        name="code"
                                        placeholder="UMUM"
                                        required
                                    />
                                    {errors.code && (
                                        <p className="text-sm text-destructive">
                                            {errors.code}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="prefix">
                                        Prefix Antrian *
                                    </Label>
                                    <Input
                                        id="prefix"
                                        name="prefix"
                                        placeholder="A"
                                        maxLength={5}
                                        required
                                    />
                                    {errors.prefix && (
                                        <p className="text-sm text-destructive">
                                            {errors.prefix}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="name">Nama Layanan *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    placeholder="Pelayanan Umum"
                                    required
                                />
                                {errors.name && (
                                    <p className="text-sm text-destructive">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Deskripsi</Label>
                                <Input
                                    id="description"
                                    name="description"
                                    placeholder="Deskripsi layanan (opsional)"
                                />
                            </div>

                            <div className="grid gap-4 md:grid-cols-3">
                                <div className="space-y-2">
                                    <Label htmlFor="average_time">
                                        Estimasi Waktu (menit) *
                                    </Label>
                                    <Input
                                        id="average_time"
                                        name="average_time"
                                        type="number"
                                        min={1}
                                        max={120}
                                        defaultValue={15}
                                        required
                                    />
                                    {errors.average_time && (
                                        <p className="text-sm text-destructive">
                                            {errors.average_time}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="max_daily_queue">
                                        Kuota Harian *
                                    </Label>
                                    <Input
                                        id="max_daily_queue"
                                        name="max_daily_queue"
                                        type="number"
                                        min={1}
                                        max={1000}
                                        defaultValue={100}
                                        required
                                    />
                                    {errors.max_daily_queue && (
                                        <p className="text-sm text-destructive">
                                            {errors.max_daily_queue}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="sort_order">Urutan</Label>
                                    <Input
                                        id="sort_order"
                                        name="sort_order"
                                        type="number"
                                        min={0}
                                        defaultValue={0}
                                    />
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-6 border-t pt-4">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="is_active"
                                        name="is_active"
                                        defaultChecked
                                    />
                                    <Label htmlFor="is_active">Aktif</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="requires_documents"
                                        name="requires_documents"
                                    />
                                    <Label htmlFor="requires_documents">
                                        Memerlukan Dokumen
                                    </Label>
                                </div>
                            </div>

                            <div className="flex justify-end gap-2 border-t pt-4">
                                <Link href="/admin/services">
                                    <Button type="button" variant="outline">
                                        Batal
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    <Save className="mr-2 size-4" />
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}
