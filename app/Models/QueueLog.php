<?php

namespace App\Models;

use App\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueLog extends Model
{
    /** @use HasFactory<\Database\Factories\QueueLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'queue_id',
        'officer_id',
        'from_status',
        'to_status',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => QueueStatus::class,
            'to_status' => QueueStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Queue, $this>
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * @return BelongsTo<Officer, $this>
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }
}
