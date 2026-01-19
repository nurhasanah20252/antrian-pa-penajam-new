<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getDayNameAttribute(): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $days[$this->day_of_week] ?? '';
    }

    public function isOpenNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        $currentDay = (int) $now->dayOfWeek;

        if ($currentDay !== $this->day_of_week) {
            return false;
        }

        $currentTime = $now->format('H:i:s');

        return $currentTime >= $this->open_time && $currentTime <= $this->close_time;
    }
}
