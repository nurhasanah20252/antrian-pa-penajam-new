<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;

class DisplayBoardController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function index(): JsonResponse
    {
        $calledQueues = $this->queueService->getCurrentlyCalledQueues();
        $statistics = $this->queueService->getTodayStatistics();

        $display = collect($calledQueues)->map(function ($item) {
            $queue = $item['queue'];

            return [
                'number' => $queue->number,
                'service' => $queue->service->name,
                'service_code' => $queue->service->code,
                'counter' => $item['counter'],
                'status' => $queue->status->value,
                'called_at' => $queue->called_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'current_queues' => $display,
                'statistics' => $statistics,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function service(Service $service): JsonResponse
    {
        $calledQueues = $this->queueService->getCurrentlyCalledQueues($service->id);
        $waitingQueues = $this->queueService->getWaitingQueues($service->id);
        $statistics = $this->queueService->getTodayStatistics($service->id);

        $display = collect($calledQueues)->map(function ($item) {
            $queue = $item['queue'];

            return [
                'number' => $queue->number,
                'counter' => $item['counter'],
                'status' => $queue->status->value,
                'called_at' => $queue->called_at?->toIso8601String(),
            ];
        });

        $waiting = $waitingQueues->map(function ($queue) {
            return [
                'number' => $queue->number,
                'is_priority' => $queue->is_priority,
                'created_at' => $queue->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'service' => [
                    'id' => $service->id,
                    'code' => $service->code,
                    'name' => $service->name,
                ],
                'current_queues' => $display,
                'waiting_queues' => $waiting,
                'statistics' => $statistics,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
