<?php

use App\QueueStatus;

describe('QueueStatus Enum', function () {
    it('has correct string values', function () {
        expect(QueueStatus::Waiting->value)->toBe('waiting')
            ->and(QueueStatus::Called->value)->toBe('called')
            ->and(QueueStatus::Processing->value)->toBe('processing')
            ->and(QueueStatus::Completed->value)->toBe('completed')
            ->and(QueueStatus::Skipped->value)->toBe('skipped')
            ->and(QueueStatus::Cancelled->value)->toBe('cancelled');
    });

    it('returns correct labels', function (QueueStatus $status, string $expectedLabel) {
        expect($status->label())->toBe($expectedLabel);
    })->with([
        'waiting' => [QueueStatus::Waiting, 'Menunggu'],
        'called' => [QueueStatus::Called, 'Dipanggil'],
        'processing' => [QueueStatus::Processing, 'Sedang Dilayani'],
        'completed' => [QueueStatus::Completed, 'Selesai'],
        'skipped' => [QueueStatus::Skipped, 'Dilewati'],
        'cancelled' => [QueueStatus::Cancelled, 'Dibatalkan'],
    ]);

    it('returns correct colors', function (QueueStatus $status, string $expectedColor) {
        expect($status->color())->toBe($expectedColor);
    })->with([
        'waiting' => [QueueStatus::Waiting, 'gray'],
        'called' => [QueueStatus::Called, 'yellow'],
        'processing' => [QueueStatus::Processing, 'blue'],
        'completed' => [QueueStatus::Completed, 'green'],
        'skipped' => [QueueStatus::Skipped, 'orange'],
        'cancelled' => [QueueStatus::Cancelled, 'red'],
    ]);

    it('correctly identifies active statuses', function (QueueStatus $status, bool $isActive) {
        expect($status->isActive())->toBe($isActive);
    })->with([
        'waiting is active' => [QueueStatus::Waiting, true],
        'called is active' => [QueueStatus::Called, true],
        'processing is active' => [QueueStatus::Processing, true],
        'completed is not active' => [QueueStatus::Completed, false],
        'skipped is not active' => [QueueStatus::Skipped, false],
        'cancelled is not active' => [QueueStatus::Cancelled, false],
    ]);
});
