<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallQueueRequest;
use App\Http\Requests\TransferQueueRequest;
use App\Http\Requests\UpdateQueueRequest;
use App\Models\Officer;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(private QueueService $queueService) {}

    public function index(Request $request): Response
    {
        $officer = $this->getOfficer($request);

        $waitingQueues = $this->queueService->getWaitingQueues($officer->service_id);
        $statistics = $this->queueService->getTodayStatistics($officer->service_id);
        $currentQueues = Queue::query()
            ->where('officer_id', $officer->id)
            ->whereIn('status', ['called', 'processing'])
            ->with('service')
            ->get();

        return Inertia::render('officer/queue/index', [
            'officer' => $officer->load('service', 'user'),
            'waitingQueues' => $waitingQueues,
            'currentQueues' => $currentQueues,
            'statistics' => $statistics,
        ]);
    }

    public function show(Request $request, Queue $queue): Response
    {
        $this->authorize('view', $queue);

        return Inertia::render('officer/queue/show', [
            'queue' => $queue->load('service', 'officer.user', 'logs.officer.user'),
        ]);
    }

    public function callNext(Request $request): RedirectResponse
    {
        $officer = $this->getOfficer($request);

        $queue = $this->queueService->callNextQueue($officer);

        if (! $queue) {
            return back()->with('warning', 'Tidak ada antrian yang menunggu.');
        }

        return back()->with('success', "Memanggil antrian {$queue->number}.");
    }

    public function call(CallQueueRequest $request, Queue $queue): RedirectResponse
    {
        $officer = $this->getOfficer($request);

        try {
            $this->queueService->callQueue($queue, $officer);

            return back()->with('success', "Memanggil antrian {$queue->number}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recall(Request $request, Queue $queue): RedirectResponse
    {
        $this->authorize('call', $queue);

        $officer = $this->getOfficer($request);

        try {
            $this->queueService->recallQueue($queue, $officer);

            return back()->with('success', "Recall antrian {$queue->number}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function process(Request $request, Queue $queue): RedirectResponse
    {
        $this->authorize('process', $queue);

        $officer = $this->getOfficer($request);

        try {
            $this->queueService->startProcessing($queue, $officer);

            return back()->with('success', "Memproses antrian {$queue->number}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(UpdateQueueRequest $request, Queue $queue): RedirectResponse
    {
        $this->authorize('complete', $queue);

        $officer = $this->getOfficer($request);

        try {
            $this->queueService->completeQueue($queue, $officer, $request->validated('notes'));

            return back()->with('success', "Antrian {$queue->number} selesai.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function skip(UpdateQueueRequest $request, Queue $queue): RedirectResponse
    {
        $this->authorize('skip', $queue);

        $officer = $this->getOfficer($request);

        try {
            $this->queueService->skipQueue($queue, $officer, $request->validated('notes'));

            return back()->with('success', "Antrian {$queue->number} dilewati.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transfer(TransferQueueRequest $request, Queue $queue): RedirectResponse
    {
        $this->authorize('transfer', $queue);

        $officer = $this->getOfficer($request);
        $targetService = Service::findOrFail($request->validated('target_service_id'));

        try {
            $newQueue = $this->queueService->transferQueue(
                $queue,
                $targetService,
                $officer,
                $request->validated('notes')
            );

            return back()->with('success', "Antrian {$queue->number} ditransfer ke {$targetService->name}. Nomor baru: {$newQueue->number}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function getOfficer(Request $request): Officer
    {
        $officer = Officer::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $officer) {
            abort(403, 'Anda tidak terdaftar sebagai petugas aktif.');
        }

        return $officer;
    }
}
