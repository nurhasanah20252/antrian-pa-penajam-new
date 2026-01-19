<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\QueueService;
use Carbon\CarbonInterface;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function index(): Response
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $displayData = $services->map(function (Service $service) {
            $currentQueues = $this->queueService->getCurrentlyCalledQueues($service->id);

            return [
                'service' => $service,
                'current_queues' => array_map(fn ($item) => [
                    'number' => $item['queue']->number,
                    'counter_number' => $item['counter'],
                    'status' => $item['queue']->status->value,
                ], $currentQueues),
                'waiting_count' => $this->queueService->getWaitingQueues($service->id)->count(),
                'last_updated' => now()->toIso8601String(),
            ];
        });

        $statistics = $this->queueService->getTodayStatistics();

        $recentlyCalled = collect($this->queueService->getRecentlyCalledQueues(5))
            ->map(function (array $queue) {
                $calledAt = $queue['called_at'];

                return [
                    ...$queue,
                    'voice_url' => route('display.voice', [
                        'number' => $queue['number'],
                        'counter' => $queue['counter'],
                        'service_name' => $queue['service_name'],
                        'called_at' => $calledAt instanceof CarbonInterface
                            ? $calledAt->toIso8601String()
                            : $calledAt,
                    ]),
                ];
            });


        return Inertia::render('display/index', [
            'services' => $displayData,
            'statistics' => $statistics,
            'recently_called' => $recentlyCalled,
            'last_updated' => now()->toIso8601String(),
        ]);
    }
}
