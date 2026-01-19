import { Head, Link, router } from '@inertiajs/react';
import { Edit, Plus, Settings, Trash2 } from 'lucide-react';

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
import type { BreadcrumbItem, Service } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Layanan', href: '/admin/services' },
];

interface ServiceWithCounts extends Service {
    officers_count: number;
    today_queues_count: number;
}

interface Props {
    services: ServiceWithCounts[];
}

export default function ServicesIndex({ services }: Props) {
    const handleDelete = (service: ServiceWithCounts) => {
        if (confirm(`Hapus layanan "${service.name}"?`)) {
            router.delete(`/admin/services/${service.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Layanan" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Kelola Layanan</h1>
                        <p className="text-muted-foreground">
                            Atur layanan antrian PTSP
                        </p>
                    </div>
                    <Link href="/admin/services/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Tambah Layanan
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {services.map((service) => (
                        <Card key={service.id}>
                            <CardHeader>
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                            <Settings className="size-5 text-primary" />
                                        </div>
                                        <div>
                                            <CardTitle className="text-lg">
                                                {service.name}
                                            </CardTitle>
                                            <CardDescription>
                                                Kode: {service.code} | Prefix:{' '}
                                                {service.prefix}
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <Badge
                                        variant={
                                            service.is_active
                                                ? 'default'
                                                : 'secondary'
                                        }
                                    >
                                        {service.is_active
                                            ? 'Aktif'
                                            : 'Nonaktif'}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {service.description && (
                                        <p className="text-sm text-muted-foreground">
                                            {service.description}
                                        </p>
                                    )}

                                    <div className="grid grid-cols-2 gap-2 text-sm">
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Petugas
                                            </p>
                                            <p className="font-bold">
                                                {service.officers_count}
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Antrian Hari Ini
                                            </p>
                                            <p className="font-bold">
                                                {service.today_queues_count}
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Est. Waktu
                                            </p>
                                            <p className="font-bold">
                                                {service.average_time} menit
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Kuota
                                            </p>
                                            <p className="font-bold">
                                                {service.max_daily_queue}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex gap-2 border-t pt-3">
                                        <Link
                                            href={`/admin/services/${service.id}/edit`}
                                            className="flex-1"
                                        >
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="w-full"
                                            >
                                                <Edit className="mr-1 size-4" />
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="text-destructive hover:bg-destructive hover:text-destructive-foreground"
                                            onClick={() =>
                                                handleDelete(service)
                                            }
                                            disabled={
                                                service.officers_count > 0 ||
                                                service.today_queues_count > 0
                                            }
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {services.length === 0 && (
                        <Card className="col-span-full">
                            <CardContent className="py-12 text-center">
                                <Settings className="mx-auto size-12 text-muted-foreground" />
                                <p className="mt-4 text-muted-foreground">
                                    Belum ada layanan. Klik tombol "Tambah
                                    Layanan" untuk memulai.
                                </p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
