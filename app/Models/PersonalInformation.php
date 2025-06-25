<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rank_id',
        'serial_no',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'address',
        'civil_status',
        'user_id',
        'age',
        'birth_date',
    ];

    protected $with = ['rank'];

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    // public function getBirthDateAtAttribute()
    // {
    //     return date('d-m-Y', strtotime($this->attributes['birth_date']));
    // }

    public function getCreatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
