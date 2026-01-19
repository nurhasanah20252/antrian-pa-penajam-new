<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Service;
use App\QueueStatus;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function index(): Response
    {
        $queues = Queue::query()
            ->with(['service:id,name,code', 'officer.user:id,name'])
            ->whereDate('created_at', today())
            ->latest()
            ->paginate(50);

        $statistics = [
            'total' => Queue::whereDate('created_at', today())->count(),
            'waiting' => Queue::whereDate('created_at', today())->where('status', QueueStatus::Waiting)->count(),
            'processing' => Queue::whereDate('created_at', today())->whereIn('status', [QueueStatus::Called, QueueStatus::Processing])->count(),
            'completed' => Queue::whereDate('created_at', today())->where('status', QueueStatus::Completed)->count(),
            'skipped' => Queue::whereDate('created_at', today())->where('status', QueueStatus::Skipped)->count(),
            'cancelled' => Queue::whereDate('created_at', today())->where('status', QueueStatus::Cancelled)->count(),
        ];

        $services = Service::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/queues/index', [
            'queues' => $queues,
            'statistics' => $statistics,
            'services' => $services,
        ]);
    }

    public function show(Queue $queue): Response
    {
        $queue->load([
            'service:id,name,code',
            'officer.user:id,name',
            'user:id,name,email',
            'logs.officer.user:id,name',
        ]);

        return Inertia::render('admin/queues/show', [
            'queue' => $queue,
        ]);
    }

    public function destroy(Queue $queue): RedirectResponse
    {
        if (in_array($queue->status, [QueueStatus::Called, QueueStatus::Processing])) {
            return back()->with('error', 'Antrian yang sedang diproses tidak dapat dihapus.');
        }

        $queue->delete();

        return redirect()->route('admin.queues.index')
            ->with('success', 'Antrian berhasil dihapus.');
    }
}
