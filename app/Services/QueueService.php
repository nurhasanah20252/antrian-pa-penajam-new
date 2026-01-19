<?php

namespace App\Services;

use App\Events\DisplayBoardUpdatedEvent;
use App\Events\QueueCalledEvent;
use App\Models\Officer;
use App\Models\Queue;
use App\Models\QueueDocument;
use App\Models\QueueLog;
use App\Models\Service;
use App\Notifications\QueueApproachingNotification;
use App\Notifications\QueueCalledNotification;
use App\Notifications\QueueRegisteredNotification;
use App\QueueStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QueueService
{
    /**
     * @param  array{name: string, nik?: string|null, phone?: string|null, email?: string|null, is_priority?: bool, source?: string, notify_email?: bool, notify_sms?: bool}  $data
     * @param  array<int, \Illuminate\Http\UploadedFile>|null  $documents
     */
    public function createQueue(Service $service, array $data, ?int $userId = null, ?array $documents = null): Queue
    {
        return DB::transaction(function () use ($service, $data, $userId, $documents) {
            $number = $this->generateQueueNumber($service);

            $queue = Queue::create([
                'number' => $number,
                'service_id' => $service->id,
                'user_id' => $userId,
                'name' => $data['name'],
                'nik' => $data['nik'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'is_priority' => $data['is_priority'] ?? false,
                'notify_email' => $data['notify_email'] ?? false,
                'notify_sms' => $data['notify_sms'] ?? false,
                'status' => QueueStatus::Waiting,
                'source' => $data['source'] ?? 'online',
                'estimated_time' => $this->calculateEstimatedTime($service),
            ]);

            $this->logStatusChange($queue, null, QueueStatus::Waiting, null, 'Antrian dibuat');

            if ($documents && count($documents) > 0) {
                $this->storeQueueDocuments($queue, $documents);
            }

            $this->sendQueueRegisteredNotification($queue);

            return $queue;
        });
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $documents
     */
    private function storeQueueDocuments(Queue $queue, array $documents): void
    {
        foreach ($documents as $document) {
            $path = Storage::putFile("queue-documents/{$queue->id}", $document);
            $originalName = $document->getClientOriginalName();
            $name = pathinfo($originalName, PATHINFO_FILENAME);

            $queue->documents()->create([
                'name' => $name,
                'original_name' => $originalName,
                'path' => $path,
                'mime_type' => $document->getClientMimeType(),
                'size' => $document->getSize(),
            ]);
        }
    }

    public function generateQueueNumber(Service $service): string
    {
        $todayCount = Queue::query()
            ->where('service_id', $service->id)
            ->whereDate('created_at', today())
            ->count();

        $nextNumber = $todayCount + 1;

        return $service->prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function calculateEstimatedTime(Service $service): int
    {
        $waitingCount = Queue::query()
            ->where('service_id', $service->id)
            ->where('status', QueueStatus::Waiting)
            ->whereDate('created_at', today())
            ->count();

        $activeOfficers = $service->getActiveOfficersCountAttribute();

        if ($activeOfficers === 0) {
            return $waitingCount * $service->average_time;
        }

        return (int) ceil($waitingCount * $service->average_time / $activeOfficers);
    }

    public function callNextQueue(Officer $officer): ?Queue
    {
        if (! $officer->canAcceptQueue()) {
            return null;
        }

        return DB::transaction(function () use ($officer) {
            $queue = Queue::query()
                ->where('service_id', $officer->service_id)
                ->where('status', QueueStatus::Waiting)
                ->whereDate('created_at', today())
                ->orderBy('is_priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (! $queue) {
                return null;
            }

            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Called,
                'officer_id' => $officer->id,
                'called_at' => now(),
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Called, $officer, 'Antrian dipanggil');

            $queue = $queue->fresh();
            $this->broadcastQueueCalled($queue);
            $this->sendQueueCalledNotification($queue);
            $this->notifyApproachingQueues($officer->service_id);

            return $queue;
        });
    }

    public function callQueue(Queue $queue, Officer $officer): Queue
    {
        if (! $officer->canAcceptQueue()) {
            throw new \RuntimeException('Officer tidak dapat menerima antrian.');
        }

        if ($queue->status !== QueueStatus::Waiting && $queue->status !== QueueStatus::Skipped) {
            throw new \RuntimeException('Antrian tidak dalam status menunggu atau dilewati.');
        }

        return DB::transaction(function () use ($queue, $officer) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Called,
                'officer_id' => $officer->id,
                'called_at' => now(),
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Called, $officer, 'Antrian dipanggil manual');

            $queue = $queue->fresh();
            $this->broadcastQueueCalled($queue);
            $this->sendQueueCalledNotification($queue);
            $this->notifyApproachingQueues($officer->service_id);

            return $queue;
        });
    }

    public function startProcessing(Queue $queue, Officer $officer): Queue
    {
        if ($queue->status !== QueueStatus::Called) {
            throw new \RuntimeException('Antrian harus dalam status dipanggil untuk mulai diproses.');
        }

        if ($queue->officer_id !== $officer->id) {
            throw new \RuntimeException('Antrian bukan milik officer ini.');
        }

        return DB::transaction(function () use ($queue, $officer) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Processing,
                'started_at' => now(),
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Processing, $officer, 'Mulai diproses');

            return $queue->fresh();
        });
    }

    public function completeQueue(Queue $queue, Officer $officer, ?string $notes = null): Queue
    {
        if ($queue->status !== QueueStatus::Processing) {
            throw new \RuntimeException('Antrian harus dalam status sedang diproses untuk diselesaikan.');
        }

        if ($queue->officer_id !== $officer->id) {
            throw new \RuntimeException('Antrian bukan milik officer ini.');
        }

        return DB::transaction(function () use ($queue, $officer, $notes) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Completed,
                'completed_at' => now(),
                'notes' => $notes,
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Completed, $officer, $notes ?? 'Antrian selesai');

            return $queue->fresh();
        });
    }

    public function skipQueue(Queue $queue, Officer $officer, ?string $notes = null): Queue
    {
        if ($queue->status !== QueueStatus::Called) {
            throw new \RuntimeException('Antrian harus dalam status dipanggil untuk dilewati.');
        }

        if ($queue->officer_id !== $officer->id) {
            throw new \RuntimeException('Antrian bukan milik officer ini.');
        }

        return DB::transaction(function () use ($queue, $officer, $notes) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Skipped,
                'notes' => $notes,
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Skipped, $officer, $notes ?? 'Antrian dilewati');

            return $queue->fresh();
        });
    }

    public function cancelQueue(Queue $queue, ?Officer $officer = null, ?string $notes = null): Queue
    {
        if (! $queue->status->isActive()) {
            throw new \RuntimeException('Antrian sudah tidak aktif.');
        }

        return DB::transaction(function () use ($queue, $officer, $notes) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Cancelled,
                'notes' => $notes,
            ]);

            $this->logStatusChange($queue, $fromStatus, QueueStatus::Cancelled, $officer, $notes ?? 'Antrian dibatalkan');

            return $queue->fresh();
        });
    }

    public function recallQueue(Queue $queue, Officer $officer): Queue
    {
        if ($queue->status !== QueueStatus::Called) {
            throw new \RuntimeException('Antrian harus dalam status dipanggil untuk recall.');
        }

        if ($queue->officer_id !== $officer->id) {
            throw new \RuntimeException('Antrian bukan milik officer ini.');
        }

        $queue->update([
            'called_at' => now(),
        ]);

        $this->logStatusChange($queue, QueueStatus::Called, QueueStatus::Called, $officer, 'Recall antrian');

        $queue = $queue->fresh();
        $this->broadcastQueueCalled($queue);
        $this->sendQueueCalledNotification($queue);

        return $queue;
    }

    /**
     * Transfer queue to another service.
     * Creates a new queue in target service and cancels the original.
     */
    public function transferQueue(Queue $queue, Service $targetService, Officer $officer, ?string $notes = null): Queue
    {
        if (! $queue->status->isActive()) {
            throw new \RuntimeException('Antrian sudah tidak aktif dan tidak dapat ditransfer.');
        }

        if ($queue->service_id === $targetService->id) {
            throw new \RuntimeException('Tidak dapat transfer ke layanan yang sama.');
        }

        if (! $targetService->is_active) {
            throw new \RuntimeException('Layanan tujuan tidak aktif.');
        }

        return DB::transaction(function () use ($queue, $targetService, $officer, $notes) {
            $fromStatus = $queue->status;
            $queue->update([
                'status' => QueueStatus::Cancelled,
                'notes' => $notes ?? 'Ditransfer ke layanan: '.$targetService->name,
            ]);

            $this->logStatusChange(
                $queue,
                $fromStatus,
                QueueStatus::Cancelled,
                $officer,
                'Ditransfer ke layanan: '.$targetService->name
            );

            $newQueue = Queue::create([
                'number' => $this->generateQueueNumber($targetService),
                'service_id' => $targetService->id,
                'user_id' => $queue->user_id,
                'transferred_from_id' => $queue->id,
                'name' => $queue->name,
                'nik' => $queue->nik,
                'phone' => $queue->phone,
                'email' => $queue->email,
                'is_priority' => $queue->is_priority,
                'notify_email' => $queue->notify_email,
                'notify_sms' => $queue->notify_sms,
                'status' => QueueStatus::Waiting,
                'source' => $queue->source,
                'estimated_time' => $this->calculateEstimatedTime($targetService),
            ]);

            $this->logStatusChange(
                $newQueue,
                null,
                QueueStatus::Waiting,
                $officer,
                'Ditransfer dari layanan: '.$queue->service->name.' (Nomor: '.$queue->number.')'
            );

            return $newQueue;
        });
    }

    /**
     * @return array<int, array{queue: Queue, counter: int|null}>
     */
    public function getCurrentlyCalledQueues(?int $serviceId = null): array
    {
        $query = Queue::query()
            ->with(['service', 'officer'])
            ->whereIn('status', [QueueStatus::Called, QueueStatus::Processing])
            ->whereDate('created_at', today())
            ->orderBy('called_at', 'desc');

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        return $query->get()->map(function (Queue $queue) {
            return [
                'queue' => $queue,
                'counter' => $queue->officer?->counter_number,
            ];
        })->toArray();
    }

    /**
     * @return array{waiting: int, called: int, processing: int, completed: int, skipped: int, cancelled: int, total: int, average_wait_time: float, average_service_time: float}
     */
    public function getTodayStatistics(?int $serviceId = null): array
    {
        $query = Queue::query()->whereDate('created_at', today());

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        $queues = $query->get();

        $completedQueues = $queues->where('status', QueueStatus::Completed);

        $avgWaitTime = 0.0;
        $avgServiceTime = 0.0;

        if ($completedQueues->isNotEmpty()) {
            $avgWaitTime = $completedQueues->avg(fn (Queue $q) => $q->waiting_time) ?? 0.0;
            $avgServiceTime = $completedQueues->avg(fn (Queue $q) => $q->service_time) ?? 0.0;
        }

        return [
            'waiting' => $queues->where('status', QueueStatus::Waiting)->count(),
            'called' => $queues->where('status', QueueStatus::Called)->count(),
            'processing' => $queues->where('status', QueueStatus::Processing)->count(),
            'completed' => $queues->where('status', QueueStatus::Completed)->count(),
            'skipped' => $queues->where('status', QueueStatus::Skipped)->count(),
            'cancelled' => $queues->where('status', QueueStatus::Cancelled)->count(),
            'total' => $queues->count(),
            'average_wait_time' => round($avgWaitTime, 1),
            'average_service_time' => round($avgServiceTime, 1),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Queue>
     */
    public function getWaitingQueues(int $serviceId): \Illuminate\Database\Eloquent\Collection
    {
        return Queue::query()
            ->where('service_id', $serviceId)
            ->where('status', QueueStatus::Waiting)
            ->whereDate('created_at', today())
            ->orderBy('is_priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function isServiceAcceptingQueue(Service $service): bool
    {
        if (! $service->is_active) {
            return false;
        }

        if (! $service->isAvailableToday()) {
            return false;
        }

        $schedule = $service->schedules()
            ->where('day_of_week', now()->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return false;
        }

        $now = now()->format('H:i:s');

        return $now >= $schedule->open_time && $now <= $schedule->close_time;
    }

    /**
     * Get queues that were called within the last N seconds.
     * Used for voice announcements on display board.
     *
     * @return array<int, array{number: string, counter: int, service_name: string, called_at: string}>
     */
    public function getRecentlyCalledQueues(int $seconds = 5): array
    {
        $since = now()->subSeconds($seconds);

        return Queue::query()
            ->with(['service:id,name', 'officer:id,counter_number'])
            ->where('status', QueueStatus::Called)
            ->where('called_at', '>=', $since)
            ->whereDate('created_at', today())
            ->orderBy('called_at', 'desc')
            ->get()
            ->map(fn (Queue $queue) => [
                'number' => $queue->number,
                'counter' => (int) ($queue->officer?->counter_number ?? 0),
                'service_name' => $queue->service?->name ?? '',
                'called_at' => $queue->called_at?->toIso8601String() ?? '',
            ])
            ->toArray();
    }

    private function logStatusChange(
        Queue $queue,
        ?QueueStatus $fromStatus,
        QueueStatus $toStatus,
        ?Officer $officer,
        ?string $notes = null
    ): void {
        QueueLog::create([
            'queue_id' => $queue->id,
            'officer_id' => $officer?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function broadcastQueueCalled(Queue $queue): void
    {
        $queue->load(['service', 'officer']);
        QueueCalledEvent::dispatch($queue);
        DisplayBoardUpdatedEvent::dispatch();
    }

    private function sendQueueRegisteredNotification(Queue $queue): void
    {
        if (! $queue->shouldNotifyByEmail() && ! $queue->shouldNotifyBySms()) {
            return;
        }

        $queue->notify(new QueueRegisteredNotification($queue));
    }

    private function sendQueueCalledNotification(Queue $queue): void
    {
        if ($queue->notified_called_at !== null) {
            return;
        }

        if (! $queue->shouldNotifyByEmail() && ! $queue->shouldNotifyBySms()) {
            return;
        }

        $counterName = $queue->officer?->counter_number
            ? 'Loket '.$queue->officer->counter_number
            : '';

        $queue->notify(new QueueCalledNotification($queue, $counterName));

        $queue->update(['notified_called_at' => now()]);
    }

    /**
     * Notify queues that are approaching (about 5 positions ahead).
     */
    private function notifyApproachingQueues(int $serviceId): void
    {
        $waitingQueues = Queue::query()
            ->where('service_id', $serviceId)
            ->where('status', QueueStatus::Waiting)
            ->whereDate('created_at', today())
            ->orderBy('is_priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        foreach ($waitingQueues as $index => $queue) {
            $position = $index + 1;

            if ($position >= 3 && $position <= 7) {
                if (($queue->shouldNotifyByEmail() || $queue->shouldNotifyBySms()) && $queue->notified_approaching_at === null) {
                    $queue->notify(new QueueApproachingNotification($queue, $position));
                    $queue->update(['notified_approaching_at' => now()]);
                }
            }
        }
    }
}
