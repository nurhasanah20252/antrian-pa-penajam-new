<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoiceAnnouncementRequest;
use App\Services\VoiceAnnouncementService;
use Illuminate\Http\JsonResponse;

class VoiceAnnouncementController extends Controller
{
    public function __construct(private VoiceAnnouncementService $voiceAnnouncementService) {}

    public function show(VoiceAnnouncementRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'audio_url' => $this->voiceAnnouncementService->getAnnouncementUrl($payload),
        ]);
    }
}
