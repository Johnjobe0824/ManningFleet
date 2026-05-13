<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExoIcasCompletion extends Model
{
    use HasFactory, SoftDeletes;

    /* ============================================================
       TABLE
    ============================================================ */
    protected $table = 'exo_icas_completions';

    /* ============================================================
       STATUSES
    ============================================================ */
    const STATUS_PENDING        = 'pending';
    const STATUS_FOR_REVIEW     = 'for_review';
    const STATUS_SCHEDULED      = 'scheduled';
    const STATUS_APPROVED       = 'approved';
    const STATUS_REJECTED       = 'rejected';
    const STATUS_ICAS_AWARDED   = 'icas_awarded';
    const STATUS_SLOT_AVAILABLE = 'slot_available';
    const STATUS_WAITING_LIST   = 'waiting_list';
    const STATUS_COMPLETED      = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_FOR_REVIEW,
        self::STATUS_SCHEDULED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_ICAS_AWARDED,
        self::STATUS_SLOT_AVAILABLE,
        self::STATUS_WAITING_LIST,
        self::STATUS_COMPLETED,
    ];

    /* ============================================================
       FILLABLE
    ============================================================ */
    protected $fillable = [
        'user_id',
        'sea_duty_record_id',
        'icas_exam_proof_id',
        'supporting_doc_id',
        'status',
        'remarks',
        'scheduled_at',

        // Step 1 checklist flags
        'checklist_sea_duty_records',
        'checklist_min_residency',
        'checklist_steaming_hours',
        'checklist_icas_exam',
    ];

    /* ============================================================
       CASTS
    ============================================================ */
    protected $casts = [
        'checklist_sea_duty_records' => 'boolean',
        'checklist_min_residency'    => 'boolean',
        'checklist_steaming_hours'   => 'boolean',
        'checklist_icas_exam'        => 'boolean',
        'scheduled_at'               => 'datetime',
    ];

    /* ============================================================
       RELATIONSHIPS
    ============================================================ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seaDutyRecord(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'sea_duty_record_id');
    }

    public function icasExamProof(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'icas_exam_proof_id');
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

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
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

    public function isIcasAwarded(): bool
    {
        return in_array($this->status, [
            self::STATUS_ICAS_AWARDED,
            self::STATUS_SLOT_AVAILABLE,
            self::STATUS_WAITING_LIST,
            self::STATUS_COMPLETED,
        ]);
    }
}