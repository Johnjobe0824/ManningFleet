<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExoReassign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exo_reassignments';

    protected $fillable = [
        'user_id',
        'assignment_orders_id',
        'performance_eval_id',
        'certification_id',
        'status',
        'scheduled_at',
        'remarks',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /* ============================================================
       STATUS CONSTANTS
    ============================================================ */
    const STATUS_PENDING    = 'pending';
    const STATUS_FOR_REVIEW = 'for_review';
    const STATUS_SCHEDULED  = 'scheduled';
    const STATUS_APPROVED   = 'approved';
    const STATUS_REJECTED   = 'rejected';
    const STATUS_COMPLETED  = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_FOR_REVIEW,
        self::STATUS_SCHEDULED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_COMPLETED,
    ];

    /* ============================================================
       HELPERS
    ============================================================ */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /* ============================================================
       RELATIONSHIPS
    ============================================================ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignmentOrders(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'assignment_orders_id');
    }

    public function performanceEval(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'performance_eval_id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'certification_id');
    }

    /* ============================================================
       SCOPES
    ============================================================ */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FOR_REVIEW);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('status',  'like', "%{$search}%")
              ->orWhere('remarks', 'like', "%{$search}%")
              ->orWhereHas('user', fn ($u) =>
                    $u->where('name', 'like', "%{$search}%")
              )
              ->orWhereHas('assignmentOrders', fn ($ao) =>
                    $ao->where('name', 'like', "%{$search}%")
              )
              ->orWhereHas('performanceEval', fn ($pe) =>
                    $pe->where('name', 'like', "%{$search}%")
              )
              ->orWhereHas('certification', fn ($c) =>
                    $c->where('name', 'like', "%{$search}%")
              );
        });
    }
}