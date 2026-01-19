<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use App\QueueStatus;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function __invoke(Request $request): Response
    {
        $todayStats = $this->queueService->getTodayStatistics();

        $serviceStats = Service::query()
            ->where('is_active', true)
            ->withCount([
                'queues as today_total' => fn ($q) => $q->whereDate('created_at', today()),
                'queues as today_waiting' => fn ($q) => $q->whereDate('created_at', today())
                    ->where('status', QueueStatus::Waiting),
                'queues as today_completed' => fn ($q) => $q->whereDate('created_at', today())
                    ->where('status', QueueStatus::Completed),
            ])
            ->get();

        $activeOfficers = Officer::query()
            ->where('is_active', true)
            ->with('user:id,name', 'service:id,name,code')
            ->get()
            ->map(fn (Officer $o) => [
                'id' => $o->id,
                'name' => $o->user->name,
                'service' => $o->service->name,
                'counter' => $o->counter_number,
                'current_queue' => Queue::query()
                    ->where('officer_id', $o->id)
                    ->whereIn('status', [QueueStatus::Called, QueueStatus::Processing])
                    ->whereDate('created_at', today())
                    ->first()?->number,
            ]);

        $recentQueues = Queue::query()
            ->with('service:id,name,code', 'officer.user:id,name')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(10)
            ->get();

        $weeklyStats = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'total' => Queue::whereDate('created_at', $date)->count(),
                'completed' => Queue::whereDate('created_at', $date)
                    ->where('status', QueueStatus::Completed)
                    ->count(),
            ];
        });

        return Inertia::render('admin/dashboard', [
            'todayStats' => $todayStats,
            'serviceStats' => $serviceStats,
            'activeOfficers' => $activeOfficers,
            'recentQueues' => $recentQueues,
            'weeklyStats' => $weeklyStats,
            'counts' => [
                'services' => Service::count(),
                'officers' => Officer::count(),
                'users' => User::count(),
                'queues_today' => $todayStats['total'],
            ],
        ]);
    }
}
