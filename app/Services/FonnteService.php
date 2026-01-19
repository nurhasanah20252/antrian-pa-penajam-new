<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.fonnte.com'
    ) {}

    public function sendMessage(string $target, string $message, array $options = []): array
    {
        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->baseUrl}/send", array_merge([
                'target' => $this->formatPhoneNumber($target),
                'message' => $message,
                'countryCode' => '62',
            ], $options));

            return $this->handleResponse($response, $target);
        } catch (\Exception $e) {
            Log::error('Fonnte WhatsApp exception', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function handleResponse(Response $response, string $target): array
    {
        $result = $response->json() ?? [];

        if ($response->successful() && ($result['status'] ?? false)) {
            Log::info('Fonnte WhatsApp sent', [
                'target' => $target,
                'response' => $result,
            ]);

            return [
                'success' => true,
                'data' => $result,
            ];
        }

        Log::error('Fonnte WhatsApp failed', [
            'target' => $target,
            'response' => $result,
        ]);

        return [
            'success' => false,
            'error' => $result['reason'] ?? 'Unknown error',
            'data' => $result,
        ];
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    public function getStatus(): array
    {
        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->baseUrl}/get-status");

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Fonnte get status exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
