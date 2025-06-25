<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
    use HasFactory, SoftDeletes;

    const OFFICER = 'Officer';

    const NON_OFFICER = 'Non-Officer';

    const NUP_CIVILIAN = 'NUP-Civilian';

    protected $fillable = [
        'level',
        'name',
        'type',
    ];

    public function getCreatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public function personal_infos()
    {
        return $this->hasMany(PersonalInformation::class);
    }
}
