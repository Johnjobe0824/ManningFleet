<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cat2CoPromotionReassign extends Model
{
    use HasFactory, SoftDeletes;

    /* ============================================================
       TABLE
    ============================================================ */
    protected $table = 'cat2_co_promotion_reassigns';

    /* ============================================================
       STATUS CONSTANTS
    ============================================================ */
    const STATUS_PENDING         = 'pending';
    const STATUS_FOR_REVIEW      = 'for_review';
    const STATUS_SCHEDULED       = 'scheduled';
    const STATUS_APPROVED        = 'approved';
    const STATUS_REJECTED        = 'rejected';
    const STATUS_SLOT_AVAILABLE  = 'slot_available';
    const STATUS_WAITING_LIST    = 'waiting_list';
    const STATUS_ORDERS_ISSUED   = 'orders_issued';
    const STATUS_ASSUMED_COMMAND = 'assumed_command';
    const STATUS_COMPLETED       = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_FOR_REVIEW,
        self::STATUS_SCHEDULED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_SLOT_AVAILABLE,
        self::STATUS_WAITING_LIST,
        self::STATUS_ORDERS_ISSUED,
        self::STATUS_ASSUMED_COMMAND,
        self::STATUS_COMPLETED,
    ];

    /* ============================================================
       MASS ASSIGNABLE
    ============================================================ */
    protected $fillable = [
        'user_id',
        'icas_award_order_id',
        'sea_duty_record_id',
        'performance_eval_id',
        'medical_certificate_id',
        'supporting_doc_id',
        'status',
        'remarks',
        'scheduled_at',
        // Step 1 checklist flags
        'checklist_icas_award_orders',
        'checklist_sea_duty_compliance',
        'checklist_performance_eval',
        'checklist_medical_fitness',
    ];

    /* ============================================================
       CASTS
    ============================================================ */
    protected $casts = [
        'scheduled_at'                 => 'datetime',
        'checklist_icas_award_orders'  => 'boolean',
        'checklist_sea_duty_compliance'=> 'boolean',
        'checklist_performance_eval'   => 'boolean',
        'checklist_medical_fitness'    => 'boolean',
    ];

    /* ============================================================
       RELATIONSHIPS
    ============================================================ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function icasAwardOrder(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'icas_award_order_id');
    }

    public function seaDutyRecord(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'sea_duty_record_id');
    }

    public function performanceEval(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'performance_eval_id');
    }

    public function medicalCertificate(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'medical_certificate_id');
    }

    public function supportingDoc(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'supporting_doc_id');
    }

    /* ============================================================
       SCOPES
    ============================================================ */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ============================================================
       HELPERS
    ============================================================ */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}