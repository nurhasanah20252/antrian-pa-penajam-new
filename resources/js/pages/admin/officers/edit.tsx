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
import type { BreadcrumbItem, Officer, Service, User } from '@/types';

interface Props {
    officer: Officer;
    availableUsers: User[];
    services: Service[];
}

export default function OfficersEdit({
    officer,
    availableUsers,
    services,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin' },
        { title: 'Petugas', href: '/admin/officers' },
        { title: 'Edit', href: `/admin/officers/${officer.id}/edit` },
    ];

    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setProcessing(true);

        const formData = new FormData(e.currentTarget);
        const data = {
            user_id: parseInt(formData.get('user_id') as string),
            service_id: parseInt(formData.get('service_id') as string),
            counter_number: parseInt(formData.get('counter_number') as string),
            max_concurrent:
                parseInt(formData.get('max_concurrent') as string) || 1,
            is_active: formData.get('is_active') === 'on',
        };

        router.put(`/admin/officers/${officer.id}`, data, {
            onError: (errs) => {
                setErrors(errs);
                setProcessing(false);
            },
            onSuccess: () => setProcessing(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Petugas - ${officer.user?.name}`} />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center gap-4">
                    <Link href="/admin/officers">
                        <Button variant="outline" size="icon">
                            <ArrowLeft className="size-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Edit Petugas</h1>
                        <p className="text-muted-foreground">
                            Perbarui informasi petugas "{officer.user?.name}"
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Petugas</CardTitle>
                            <CardDescription>
                                Perbarui detail petugas
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="user_id">
                                    Pilih Pengguna *
                                </Label>
                                <select
                                    id="user_id"
                                    name="user_id"
                                    required
                                    defaultValue={officer.user_id}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="">
                                        -- Pilih Pengguna --
                                    </option>
                                    {availableUsers.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} ({user.email}) -{' '}
                                            {user.role}
                                        </option>
                                    ))}
                                </select>
                                {errors.user_id && (
                                    <p className="text-sm text-destructive">
                                        {errors.user_id}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="service_id">
                                    Layanan yang Ditangani *
                                </Label>
                                <select
                                    id="service_id"
                                    name="service_id"
                                    required
                                    defaultValue={officer.service_id}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="">
                                        -- Pilih Layanan --
                                    </option>
                                    {services.map((service) => (
                                        <option
                                            key={service.id}
                                            value={service.id}
                                        >
                                            {service.name} ({service.code})
                                        </option>
                                    ))}
                                </select>
                                {errors.service_id && (
                                    <p className="text-sm text-destructive">
                                        {errors.service_id}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="counter_number">
                                        Nomor Loket *
                                    </Label>
                                    <Input
                                        id="counter_number"
                                        name="counter_number"
                                        type="number"
                                        min={1}
                                        max={99}
                                        defaultValue={officer.counter_number}
                                        required
                                    />
                                    {errors.counter_number && (
                                        <p className="text-sm text-destructive">
                                            {errors.counter_number}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="max_concurrent">
                                        Maks. Antrian Bersamaan
                                    </Label>
                                    <Input
                                        id="max_concurrent"
                                        name="max_concurrent"
                                        type="number"
                                        min={1}
                                        max={10}
                                        defaultValue={officer.max_concurrent}
                                    />
                                    {errors.max_concurrent && (
                                        <p className="text-sm text-destructive">
                                            {errors.max_concurrent}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-6 border-t pt-4">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="is_active"
                                        name="is_active"
                                        defaultChecked={officer.is_active}
                                    />
                                    <Label htmlFor="is_active">Aktif</Label>
                                </div>
                            </div>

                            <div className="flex justify-end gap-2 border-t pt-4">
                                <Link href="/admin/officers">
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
