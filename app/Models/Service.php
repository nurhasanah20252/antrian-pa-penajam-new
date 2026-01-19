<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'prefix',
        'average_time',
        'max_daily_queue',
        'is_active',
        'requires_documents',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'average_time' => 'integer',
            'max_daily_queue' => 'integer',
            'is_active' => 'boolean',
            'requires_documents' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Officer, $this>
     */
    public function officers(): HasMany
    {
        return $this->hasMany(Officer::class);
    }

    /**
     * @return HasMany<Queue, $this>
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * @return HasMany<ServiceSchedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function getActiveOfficersCountAttribute(): int
    {
        return $this->officers()->where('is_active', true)->where('is_available', true)->count();
    }

    public function getTodayQueueCountAttribute(): int
    {
        return $this->queues()->whereDate('created_at', today())->count();
    }

    public function isAvailableToday(): bool
    {
        return $this->is_active && $this->getTodayQueueCountAttribute() < $this->max_daily_queue;
    }
}
