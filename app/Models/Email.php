<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_card_id', 'type', 'email',
    ];

    public function businessCard()
    {
        return $this->belongsTo(PersonalBusinessCard::class, 'business_card_id');
    }
}
