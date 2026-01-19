<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfficerRequest;
use App\Http\Requests\Admin\UpdateOfficerRequest;
use App\Models\Officer;
use App\Models\Service;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OfficerController extends Controller
{
    public function index(): Response
    {
        $officers = Officer::query()
            ->with(['user:id,name,email', 'service:id,name,code'])
            ->orderBy('service_id')
            ->orderBy('counter_number')
            ->get();

        return Inertia::render('admin/officers/index', [
            'officers' => $officers,
        ]);
    }

    public function create(): Response
    {
        $officerRoles = [
            UserRole::PetugasUmum,
            UserRole::PetugasPosbakum,
            UserRole::PetugasPembayaran,
        ];

        $existingOfficerUserIds = Officer::pluck('user_id')->toArray();

        $availableUsers = User::query()
            ->whereIn('role', $officerRoles)
            ->whereNotIn('id', $existingOfficerUserIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/officers/create', [
            'availableUsers' => $availableUsers,
            'services' => $services,
        ]);
    }

    public function store(StoreOfficerRequest $request): RedirectResponse
    {
        Officer::create($request->validated());

        return redirect()->route('admin.officers.index')
            ->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function show(Officer $officer): Response
    {
        $officer->load(['user:id,name,email,role', 'service:id,name,code']);

        return Inertia::render('admin/officers/show', [
            'officer' => $officer,
        ]);
    }

    public function edit(Officer $officer): Response
    {
        $officer->load(['user:id,name,email']);

        $officerRoles = [
            UserRole::PetugasUmum,
            UserRole::PetugasPosbakum,
            UserRole::PetugasPembayaran,
        ];

        $existingOfficerUserIds = Officer::where('id', '!=', $officer->id)
            ->pluck('user_id')
            ->toArray();

        $availableUsers = User::query()
            ->whereIn('role', $officerRoles)
            ->where(function ($q) use ($existingOfficerUserIds, $officer) {
                $q->whereNotIn('id', $existingOfficerUserIds)
                    ->orWhere('id', $officer->user_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/officers/edit', [
            'officer' => $officer,
            'availableUsers' => $availableUsers,
            'services' => $services,
        ]);
    }

    public function update(UpdateOfficerRequest $request, Officer $officer): RedirectResponse
    {
        $officer->update($request->validated());

        return redirect()->route('admin.officers.index')
            ->with('success', 'Petugas berhasil diperbarui.');
    }

    public function destroy(Officer $officer): RedirectResponse
    {
        $officer->delete();

        return redirect()->route('admin.officers.index')
            ->with('success', 'Petugas berhasil dihapus.');
    }
}
