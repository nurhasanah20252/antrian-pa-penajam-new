<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VoiceAnnouncementService
{
    public function __construct(private string $nodeBinary = 'node') {}

    public function getAnnouncementUrl(array $payload): string
    {
        $hash = $this->payloadHash($payload);
        $relativePath = "voice/{$hash}.mp3";
        $fullPath = storage_path("app/public/{$relativePath}");

        if (! File::exists($fullPath)) {
            $this->ensureDirectory(dirname($fullPath));
            $this->generateAudio($payload, $fullPath);
        }

        return asset("storage/{$relativePath}");
    }

    private function generateAudio(array $payload, string $fullPath): void
    {
        $scriptPath = base_path('resources/scripts/tts-generate.mjs');
        $message = $this->formatMessage($payload);

        $process = new Process([
            $this->nodeBinary,
            $scriptPath,
            $message,
            $fullPath,
        ]);

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'Gagal membuat audio TTS.');
        }
    }

    private function formatMessage(array $payload): string
    {
        $number = $this->formatQueueNumber($payload['number']);

        return "Nomor antrian {$number}, silakan menuju loket {$payload['counter']}";
    }

    private function formatQueueNumber(string $number): string
    {
        return collect(mb_str_split($number))
            ->map(fn (string $char) => preg_match('/[A-Z]/', $char) ? $char.' ' : $char)
            ->implode(' ');
    }

    private function payloadHash(array $payload): string
    {
        $fingerprint = Str::lower(trim($payload['number']))
            .'|'.(string) $payload['counter']
            .'|'.Str::lower(trim($payload['service_name']))
            .'|'.(string) $payload['called_at'];

        return hash('sha256', $fingerprint);
    }

    private function ensureDirectory(string $path): void
    {
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}
