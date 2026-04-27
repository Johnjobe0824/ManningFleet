<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cat2ExoPromotion extends Model
{
    use HasFactory, SoftDeletes;

    /* ============================================================
       TABLE
    ============================================================ */
    protected $table = 'cat2_exo_promotions';

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
       FILLABLE
    ============================================================ */
    protected $fillable = [
        'user_id',
        'assignment_orders_id',
        'performance_eval_id',
        'certification_id',

        // Step 1 checklist flags
        'checklist_sea_duty_records',
        'checklist_min_residency',
        'checklist_steaming_hours',
        'checklist_cat1_compliance',

        // Admin-managed
        'status',
        'remarks',
        'scheduled_at',
    ];

    /* ============================================================
       CASTS
    ============================================================ */
    protected $casts = [
        'checklist_sea_duty_records' => 'boolean',
        'checklist_min_residency'    => 'boolean',
        'checklist_steaming_hours'   => 'boolean',
        'checklist_cat1_compliance'  => 'boolean',
        'scheduled_at'               => 'datetime',
    ];

    /* ============================================================
       RELATIONSHIPS
    ============================================================ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sea Duty Records (maps to documents table)
     */
    public function assignmentOrders(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'assignment_orders_id');
    }

    /**
     * Formal Promotion Request Letter (maps to documents table)
     */
    public function performanceEval(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'performance_eval_id');
    }

    /**
     * Supporting Certifications (optional)
     */
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

    /* ============================================================
       HELPERS
    ============================================================ */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function checklistComplete(): bool
    {
        return $this->checklist_sea_duty_records
            && $this->checklist_min_residency
            && $this->checklist_steaming_hours
            && $this->checklist_cat1_compliance;
    }
}