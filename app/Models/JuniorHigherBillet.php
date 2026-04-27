<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JuniorHigherBillet extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'junior_higher_billets';
    protected $fillable = [
        'user_id',
        'sea_duty_record_id',       // → documents.id
        'ships_order_id',           // → chips_reports.id
        'endorsement_id',           // → chips_reports.id
        'duty_performance_cert_id', // → certificates.id
        'jsolc_cert_id',            // → certificates.id
        'watchstander_cert_id',     // → certificates.id (nullable / optional)
        'status',
        'remarks',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

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

    // Endorsement → chips_reports table
    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(ChipsReport::class, 'endorsement_id');
    }

    // Duty Performance Cert → certificates table (model: Certification)
    public function dutyPerformanceCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'duty_performance_cert_id');
    }

    // JSOLC Cert → certificates table (model: Certification)
    public function jsolcCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'jsolc_cert_id');
    }

    // Watch Stander Cert (optional) → certificates table (model: Certification)
    public function watchstanderCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'watchstander_cert_id');
    }
}