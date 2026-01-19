<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $services = Service::query()
            ->withCount([
                'officers',
                'queues as today_queues_count' => fn ($q) => $q->whereDate('created_at', today()),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/services/index', [
            'services' => $services,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/services/create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function show(Service $service): Response
    {
        $service->loadCount([
            'officers',
            'queues as today_queues_count' => fn ($q) => $q->whereDate('created_at', today()),
            'queues as total_queues_count',
        ]);

        $service->load(['officers.user:id,name', 'schedules']);

        return Inertia::render('admin/services/show', [
            'service' => $service,
        ]);
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('admin/services/edit', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->queues()->exists()) {
            return back()->with('error', 'Layanan tidak dapat dihapus karena masih memiliki antrian.');
        }

        if ($service->officers()->exists()) {
            return back()->with('error', 'Layanan tidak dapat dihapus karena masih memiliki petugas.');
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
