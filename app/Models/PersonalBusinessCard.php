<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalBusinessCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'fio', 'about_me', 'company_name', 'job_position', 'photo', 'main_info'
    ];

    protected $casts = [
        'main_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function phones()
    {
        return $this->hasMany(Phone::class, 'business_card_id');
    }

    public function emails()
    {
        return $this->hasMany(Email::class, 'business_card_id');
    }

    public function addresses()
    {
        return $this->hasMany(Phone::class, 'business_card_id');
    }

    public function websites()
    {
        return $this->hasMany(Phone::class, 'business_card_id');
    }
}
