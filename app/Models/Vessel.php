<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vessel extends Model
{
    use HasFactory, SoftDeletes;

    const I = 'Category I';

    const II = 'Category II';

    const III = 'Category III';

    const RFS = 'Ready For Sea';

    const NRFS = 'Not Ready For Sea';

    protected $fillable = [
        'vessel_name',
        'vessel_bow_no',
        'category',
        'size',
        'status',
    ];

    protected $with = ['assignedPositions'];

    public function getCreatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public function assignedPositions()
    {
        return $this->hasMany(VesselPosition::class);
    }
}
