<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExoDeliberation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exo_deliberations';

    protected $fillable = [
        'user_id',
        'junior_billet_completion_id',
        'slmc_endorsement_id',
        'slmc_orders_id',
        'slmc_graduation_cert_id',
        'status',
        'remarks',
        'scheduled_at',
    ];

    protected $casts = [
        'status' => 'string',
        'scheduled_at' => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Junior Billet Completion → certifications table
    public function juniorBilletCompletion(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'junior_billet_completion_id');
    }

    // Approved SLMC Endorsement → certifications table
    public function slmcEndorsement(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'slmc_endorsement_id');
    }

    // Orders to Attend SLMC → certifications table
    public function slmcOrders(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'slmc_orders_id');
    }

    // SLMC Graduation Certificate → certifications table
    public function slmcGraduationCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'slmc_graduation_cert_id');
    }

    /* ── Scopes ────────────────────────────────────────── */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ── Helpers ───────────────────────────────────────── */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}