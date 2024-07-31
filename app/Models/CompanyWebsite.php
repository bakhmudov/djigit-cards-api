<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyWebsite extends Model
{
    use HasFactory;

    protected $fillable = ['company_business_card_id', 'type', 'url'];

    public function company()
    {
        return $this->belongsTo(CompanyBusinessCard::class);
    }
}
