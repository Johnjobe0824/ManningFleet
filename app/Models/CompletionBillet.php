<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompletionBillet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'completion_billets';

    protected $fillable = [
        'user_id',
        'sea_duty_record_id',
        'ships_order_id',
        'engro_dgo_cert_id',
        'watchstander_cert_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Sea Duty Record → documents table
    public function seaDutyRecord(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'sea_duty_record_id');
    }

    // Ships Order → chips_reports table
    public function shipsOrder(): BelongsTo
    {
        return $this->belongsTo(ChipsReport::class, 'ships_order_id');
    }

    // ENGRO / DGO Cert → certificates table
    public function engroDgoCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'engro_dgo_cert_id');
    }

    // Watch Stander Cert → certificates table
    public function watchstanderCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'watchstander_cert_id');
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

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}