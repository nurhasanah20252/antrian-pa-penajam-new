<?php

namespace App\Events;

use App\Services\QueueService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayBoardUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array{waiting: int, called: int, processing: int, completed: int, skipped: int, cancelled: int, total: int, average_wait_time: float, average_service_time: float}
     */
    public array $statistics;

    public string $lastUpdated;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $queueService = app(QueueService::class);
        $this->statistics = $queueService->getTodayStatistics();
        $this->lastUpdated = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('display'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'display.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'statistics' => $this->statistics,
            'last_updated' => $this->lastUpdated,
        ];
    }
}
