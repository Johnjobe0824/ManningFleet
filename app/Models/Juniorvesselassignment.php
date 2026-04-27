<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JuniorVesselAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'junior_vessel_assignments';

    protected $fillable = [
        'user_id',
        'carry_morning_report_id',
        'certificate_mso_dco_id',
        'certificate_jsolc_id',
        'vessel_id',
        'position_id',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carryMorningReport()
    {
        return $this->belongsTo(ChipsReport::class, 'carry_morning_report_id', 'id');
    }

    public function certificateMsoDco()
    {
        return $this->belongsTo(Certification::class, 'certificate_mso_dco_id', 'id');
    }

    public function certificateJsolc()
    {
        return $this->belongsTo(Certification::class, 'certificate_jsolc_id', 'id');
    }

    public function vessel()
    {
        return $this->belongsTo(Vessel::class, 'vessel_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}