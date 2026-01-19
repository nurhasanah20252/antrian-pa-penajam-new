<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCalledEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $queueNumber;

    public int $counterNumber;

    public string $serviceName;

    public string $calledAt;

    /**
     * Create a new event instance.
     */
    public string $voiceUrl;

    public function __construct(Queue $queue)
    {
        $this->queueNumber = $queue->number;
        $this->counterNumber = $queue->officer?->counter_number ?? 0;
        $this->serviceName = $queue->service?->name ?? '';
        $this->calledAt = $queue->called_at?->toIso8601String() ?? now()->toIso8601String();
        $this->voiceUrl = route('display.voice', [
            'number' => $this->queueNumber,
            'counter' => $this->counterNumber,
            'service_name' => $this->serviceName,
            'called_at' => $this->calledAt,
        ]);
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
        return 'queue.called';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'number' => $this->queueNumber,
            'counter' => $this->counterNumber,
            'service_name' => $this->serviceName,
            'called_at' => $this->calledAt,
            'voice_url' => $this->voiceUrl,
        ];
    }
}
