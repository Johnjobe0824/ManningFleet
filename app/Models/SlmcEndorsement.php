<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlmcEndorsement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'slmc_endorsements';

    protected $fillable = [
        'user_id',
        'sea_duty_record_id',
        'ships_order_id',
        'graduation_cert_id',
        'watchstander_cert_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                       */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seaDutyRecord(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'sea_duty_record_id');
    }

    public function shipsOrder(): BelongsTo
    {
        return $this->belongsTo(ChipsReport::class, 'ships_order_id');
    }

    public function graduationCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'graduation_cert_id');
    }

    public function watchstanderCert(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'watchstander_cert_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                              */
    /* ------------------------------------------------------------------ */

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
}