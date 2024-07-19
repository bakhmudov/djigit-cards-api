<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_card_id', 'type', 'number',
    ];

    public function businessCard()
    {
        return $this->belongsTo(PersonalBusinessCard::class, 'business_card_id');
    }
}
