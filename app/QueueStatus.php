<?php

namespace App;

enum QueueStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case Processing = 'processing';
    case Completed = 'completed';
    case Skipped = 'skipped';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Menunggu',
            self::Called => 'Dipanggil',
            self::Processing => 'Sedang Dilayani',
            self::Completed => 'Selesai',
            self::Skipped => 'Dilewati',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Waiting => 'gray',
            self::Called => 'yellow',
            self::Processing => 'blue',
            self::Completed => 'green',
            self::Skipped => 'orange',
            self::Cancelled => 'red',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Waiting, self::Called, self::Processing]);
    }
}
