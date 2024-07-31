<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;

    protected $fillable = ['company_business_card_id', 'type', 'address'];

    public function company()
    {
        return $this->belongsTo(CompanyBusinessCard::class);
    }
}
