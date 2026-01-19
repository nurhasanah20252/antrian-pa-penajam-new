<?php

namespace App\Models;

use App\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Queue extends Model
{
    /** @use HasFactory<\Database\Factories\QueueFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'number',
        'service_id',
        'user_id',
        'officer_id',
        'transferred_from_id',
        'name',
        'nik',
        'phone',
        'email',
        'notify_email',
        'notify_sms',
        'is_priority',
        'status',
        'source',
        'estimated_time',
        'called_at',
        'started_at',
        'completed_at',
        'notes',
        'notified_approaching_at',
        'notified_called_at',
    ];

    protected function casts(): array
    {
        return [
            'is_priority' => 'boolean',
            'notify_email' => 'boolean',
            'notify_sms' => 'boolean',
            'status' => QueueStatus::class,
            'estimated_time' => 'integer',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'notified_approaching_at' => 'datetime',
            'notified_called_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Officer, $this>
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    /**
     * @return HasMany<QueueLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(QueueLog::class);
    }

    /**
     * @return BelongsTo<Queue, $this>
     */
    public function transferredFrom(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'transferred_from_id');
    }

    /**
     * @return HasMany<Queue, $this>
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Queue::class, 'transferred_from_id');
    }

    /**
     * @return HasMany<QueueDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(QueueDocument::class);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isWaiting(): bool
    {
        return $this->status === QueueStatus::Waiting;
    }

    public function getWaitingTimeAttribute(): ?int
    {
        if ($this->called_at) {
            return $this->created_at->diffInMinutes($this->called_at);
        }

        return $this->created_at->diffInMinutes(now());
    }

    public function getServiceTimeAttribute(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return string|null
     */
    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }

    /**
     * Check if this queue should receive email notifications.
     */
    public function shouldNotifyByEmail(): bool
    {
        return $this->notify_email && ! empty($this->email);
    }

    /**
     * Check if this queue should receive SMS notifications.
     */
    public function shouldNotifyBySms(): bool
    {
        return $this->notify_sms && ! empty($this->phone);
    }
}
