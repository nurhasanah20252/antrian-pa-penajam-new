<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQueueRequest;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function index(): Response
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'code' => $service->code,
                    'name' => $service->name,
                    'description' => $service->description,
                    'average_time' => $service->average_time,
                    'is_available' => $this->queueService->isServiceAcceptingQueue($service),
                    'today_queue_count' => $service->today_queue_count,
                    'max_daily_queue' => $service->max_daily_queue,
                ];
            });

        return Inertia::render('kiosk/index', [
            'services' => $services,
        ]);
    }

    public function store(CreateQueueRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $service = Service::findOrFail($validated['service_id']);

        $queue = $this->queueService->createQueue(
            service: $service,
            data: [
                'name' => $validated['name'],
                'nik' => $validated['nik'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_priority' => $validated['is_priority'] ?? false,
                'notify_email' => $validated['notify_email'] ?? false,
                'source' => 'kiosk',
            ],
            userId: null,
        );

        return redirect()->route('kiosk.tiket', $queue);
    }

    public function tiket(Queue $queue): Response
    {
        $position = $this->queueService->getWaitingQueues($queue->service_id)
            ->filter(fn (Queue $q) => $q->created_at <= $queue->created_at)
            ->count();

        $estimatedWait = $position * ($queue->service->average_time ?? 5);

        return Inertia::render('kiosk/tiket', [
            'queue' => $queue->load('service'),
            'position' => $position,
            'estimated_wait' => $estimatedWait,
        ]);
    }
}
