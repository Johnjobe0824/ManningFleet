<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cat3ExoPromotion extends Model
{
    use SoftDeletes;

    protected $table = 'cat3_exo_promotions';

    /* ------------------------------------------------------------------ */
    /* Constants                                                           */
    /* ------------------------------------------------------------------ */

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

    /* ------------------------------------------------------------------ */
    /* Mass assignment                                                     */
    /* ------------------------------------------------------------------ */

    protected $fillable = [
        'user_id',

        // Documents
        'assignment_orders_id',
        'performance_eval_id',
        'certification_id',

        // Checklist
        'checklist_sea_duty_records',
        'checklist_min_residency',
        'checklist_steaming_hours',

        // Workflow
        'status',
        'remarks',
        'scheduled_at',
    ];

    protected $casts = [
        'checklist_sea_duty_records' => 'boolean',
        'checklist_min_residency'    => 'boolean',
        'checklist_steaming_hours'   => 'boolean',
        'scheduled_at'               => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /* Relationships                                                        */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sea Duty Records (required) — stored in the documents table.
     * Named "assignmentOrders" to match the front-end field naming.
     */
    public function assignmentOrders(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'assignment_orders_id');
    }

    /**
     * Formal Promotion Request Letter (optional) — stored in the documents table.
     * Named "performanceEval" to match the front-end field naming.
     */
    public function performanceEval(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'performance_eval_id');
    }

    /**
     * Supporting Certifications (optional).
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'certification_id');
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_COMPLETED]);
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}