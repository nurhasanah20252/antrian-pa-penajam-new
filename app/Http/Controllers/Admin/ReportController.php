<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Service;
use App\QueueStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReportController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $serviceId = $request->get('service_id');

        $query = Queue::query()
            ->with('service:id,name,code', 'officer.user:id,name')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        $queues = $query->latest()->paginate(50)->withQueryString();

        $summary = [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', QueueStatus::Completed)->count(),
            'cancelled' => (clone $query)->where('status', QueueStatus::Cancelled)->count(),
            'skipped' => (clone $query)->where('status', QueueStatus::Skipped)->count(),
            'average_wait_time' => $this->calculateAverageWaitTime(clone $query),
            'average_service_time' => $this->calculateAverageServiceTime(clone $query),
        ];

        $dailyStats = Queue::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($serviceId, fn ($q) => $q->where('service_id', $serviceId))
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return Inertia::render('admin/reports/index', [
            'queues' => $queues,
            'summary' => $summary,
            'dailyStats' => $dailyStats,
            'services' => Service::query()->where('is_active', true)->get(['id', 'name', 'code']),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'service_id' => $serviceId,
            ],
        ]);
    }

    public function export(Request $request): Response
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $serviceId = $request->get('service_id');

        $query = Queue::query()
            ->with('service:id,name,code', 'officer.user:id,name')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        $queues = $query->latest()->get();

        $csv = "Nomor Antrian,Layanan,Nama,Status,Petugas,Loket,Waktu Daftar,Waktu Panggil,Waktu Mulai,Waktu Selesai\n";

        foreach ($queues as $queue) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $queue->number,
                $queue->service?->name ?? '-',
                str_replace(',', ' ', $queue->name),
                $queue->status->value,
                $queue->officer?->user?->name ?? '-',
                $queue->officer?->counter_number ?? '-',
                $queue->created_at->format('Y-m-d H:i:s'),
                $queue->called_at?->format('Y-m-d H:i:s') ?? '-',
                $queue->started_at?->format('Y-m-d H:i:s') ?? '-',
                $queue->completed_at?->format('Y-m-d H:i:s') ?? '-',
            );
        }

        $filename = "laporan-antrian-{$startDate}-{$endDate}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Calculate average wait time (created_at to called_at) in minutes.
     * Uses PHP calculation for database-agnostic compatibility.
     */
    private function calculateAverageWaitTime($query): ?float
    {
        $queues = $query
            ->where('status', QueueStatus::Completed)
            ->whereNotNull('called_at')
            ->get(['created_at', 'called_at']);

        if ($queues->isEmpty()) {
            return null;
        }

        $avgMinutes = $queues->avg(fn ($q) => $q->called_at->diffInMinutes($q->created_at));

        return round($avgMinutes, 1);
    }

    /**
     * Calculate average service time (started_at to completed_at) in minutes.
     * Uses PHP calculation for database-agnostic compatibility.
     */
    private function calculateAverageServiceTime($query): ?float
    {
        $queues = $query
            ->where('status', QueueStatus::Completed)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get(['started_at', 'completed_at']);

        if ($queues->isEmpty()) {
            return null;
        }

        $avgMinutes = $queues->avg(fn ($q) => $q->completed_at->diffInMinutes($q->started_at));

        return round($avgMinutes, 1);
    }
}
