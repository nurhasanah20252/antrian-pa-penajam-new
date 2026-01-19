<?php

namespace App\Models;

use App\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Officer extends Model
{
    /** @use HasFactory<\Database\Factories\OfficerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'counter_number',
        'is_active',
        'is_available',
        'max_concurrent',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'max_concurrent' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getCurrentQueueCountAttribute(): int
    {
        return Queue::query()
            ->where('officer_id', $this->id)
            ->where('status', QueueStatus::Processing)
            ->count();
    }

    public function canAcceptQueue(): bool
    {
        return $this->is_active
            && $this->is_available
            && $this->getCurrentQueueCountAttribute() < $this->max_concurrent;
    }
}
