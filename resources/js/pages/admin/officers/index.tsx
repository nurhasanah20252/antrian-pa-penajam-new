import { Head, Link, router } from '@inertiajs/react';
import { Edit, Plus, Trash2, UserCog, Users } from 'lucide-react';

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
import type { BreadcrumbItem, Officer } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Petugas', href: '/admin/officers' },
];

interface Props {
    officers: Officer[];
}

export default function OfficersIndex({ officers }: Props) {
    const handleDelete = (officer: Officer) => {
        if (confirm(`Hapus petugas "${officer.user?.name}"?`)) {
            router.delete(`/admin/officers/${officer.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Petugas" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Kelola Petugas</h1>
                        <p className="text-muted-foreground">
                            Atur petugas pelayanan PTSP
                        </p>
                    </div>
                    <Link href="/admin/officers/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            Tambah Petugas
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {officers.map((officer) => (
                        <Card key={officer.id}>
                            <CardHeader>
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                            <UserCog className="size-5 text-primary" />
                                        </div>
                                        <div>
                                            <CardTitle className="text-lg">
                                                {officer.user?.name ?? '-'}
                                            </CardTitle>
                                            <CardDescription>
                                                {officer.user?.email}
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <Badge
                                            variant={
                                                officer.is_active
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {officer.is_active
                                                ? 'Aktif'
                                                : 'Nonaktif'}
                                        </Badge>
                                        {officer.is_available && (
                                            <Badge
                                                variant="outline"
                                                className="text-green-600"
                                            >
                                                Online
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <div className="grid grid-cols-2 gap-2 text-sm">
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Layanan
                                            </p>
                                            <p className="font-bold">
                                                {officer.service?.name ?? '-'}
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Loket
                                            </p>
                                            <p className="font-bold">
                                                {officer.counter_number}
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Kode Layanan
                                            </p>
                                            <p className="font-bold">
                                                {officer.service?.code ?? '-'}
                                            </p>
                                        </div>
                                        <div className="rounded border p-2 text-center">
                                            <p className="text-muted-foreground">
                                                Maks. Antrian
                                            </p>
                                            <p className="font-bold">
                                                {officer.max_concurrent}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex gap-2 border-t pt-3">
                                        <Link
                                            href={`/admin/officers/${officer.id}/edit`}
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
                                                handleDelete(officer)
                                            }
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {officers.length === 0 && (
                        <Card className="col-span-full">
                            <CardContent className="py-12 text-center">
                                <Users className="mx-auto size-12 text-muted-foreground" />
                                <p className="mt-4 text-muted-foreground">
                                    Belum ada petugas. Klik tombol "Tambah
                                    Petugas" untuk memulai.
                                </p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
