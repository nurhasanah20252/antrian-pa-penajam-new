<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQueueRequest;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;

class QueueController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function services(): JsonResponse
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
                    'prefix' => $service->prefix,
                    'average_time' => $service->average_time,
                    'is_available' => $this->queueService->isServiceAcceptingQueue($service),
                    'waiting_count' => $this->queueService->getWaitingQueues($service->id)->count(),
                    'estimated_wait' => $this->queueService->calculateEstimatedTime($service),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function store(CreateQueueRequest $request): JsonResponse
    {
        $service = Service::findOrFail($request->validated('service_id'));

        $queue = $this->queueService->createQueue(
            $service,
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Antrian berhasil dibuat.',
            'data' => [
                'id' => $queue->id,
                'number' => $queue->number,
                'service' => $service->name,
                'estimated_time' => $queue->estimated_time,
                'position' => $this->queueService->getWaitingQueues($service->id)
                    ->filter(fn ($q) => $q->id <= $queue->id)->count(),
            ],
        ], 201);
    }

    public function show(Queue $queue): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $queue->id,
                'number' => $queue->number,
                'service' => $queue->service->name,
                'status' => $queue->status->value,
                'status_label' => $queue->status->label(),
                'is_priority' => $queue->is_priority,
                'position' => $queue->status->value === 'waiting'
                    ? $this->queueService->getWaitingQueues($queue->service_id)
                        ->filter(fn ($q) => $q->id <= $queue->id)->count()
                    : null,
                'counter' => $queue->officer?->counter_number,
                'waiting_time' => $queue->waiting_time,
                'created_at' => $queue->created_at->toIso8601String(),
                'called_at' => $queue->called_at?->toIso8601String(),
            ],
        ]);
    }

    public function cancel(Queue $queue): JsonResponse
    {
        if ($queue->user_id !== request()->user()?->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membatalkan antrian ini.',
            ], 403);
        }

        try {
            $this->queueService->cancelQueue($queue, null, 'Dibatalkan oleh pengguna');

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dibatalkan.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
