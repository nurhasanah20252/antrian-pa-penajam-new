<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQueueRequest;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AntrianController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function create(): Response
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

        return Inertia::render('antrian/daftar', [
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
                'notify_sms' => $validated['notify_sms'] ?? false,
                'source' => 'online',
            ],
            userId: $request->user()?->id,
            documents: $request->file('documents'),
        );

        return redirect()->route('antrian.tiket', $queue);
    }

    public function tiket(Queue $queue): Response
    {
        $position = $this->queueService->getWaitingQueues($queue->service_id)
            ->filter(fn (Queue $q) => $q->created_at <= $queue->created_at)
            ->count();

        $estimatedWait = $position * ($queue->service->average_time ?? 5);

        return Inertia::render('antrian/tiket', [
            'queue' => $queue->load('service'),
            'position' => $position,
            'estimated_wait' => $estimatedWait,
        ]);
    }

    public function status(): Response
    {
        return Inertia::render('antrian/status');
    }

    public function cekStatus(string $number): Response
    {
        $queue = Queue::query()
            ->where('number', strtoupper($number))
            ->whereDate('created_at', today())
            ->with('service', 'officer.user')
            ->first();

        if (! $queue) {
            return Inertia::render('antrian/status', [
                'error' => 'Nomor antrian tidak ditemukan untuk hari ini.',
                'searched_number' => $number,
            ]);
        }

        $position = 0;
        if ($queue->isWaiting()) {
            $position = $this->queueService->getWaitingQueues($queue->service_id)
                ->filter(fn (Queue $q) => $q->created_at <= $queue->created_at)
                ->count();
        }

        return Inertia::render('antrian/status', [
            'queue' => $queue,
            'position' => $position,
            'searched_number' => $number,
        ]);
    }
}
