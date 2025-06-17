<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{
    User,
    Vessel,
    Position
};

class SeaDutyRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vessel_id',
        'position_id',
        'date_from',
        'date_to',
        'authority',
        'tst',
        'tmc',
        'status',
        'is_active'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
