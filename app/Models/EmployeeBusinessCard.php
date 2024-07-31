<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBusinessCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_business_card_id',
        'photo', 'fio', 'job_position',
        'main_info', 'phones', 'emails',
        'addresses', 'websites',
    ];

    protected $casts = [
        'main_info' => 'array',
        'phones' => 'array',
        'emails' => 'array',
        'addresses' => 'array',
        'websites' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(CompanyBusinessCard::class);
    }
}
