<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBusinessCard extends Model
{
    use HasFactory;

    protected $fillable = ['logo', 'name', 'description'];

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(companyAddress::class);
    }

    public function websites()
    {
        $this->hasMany(companyWebsite::class);
    }

    public function employees()
    {
        $this->hasMany(EmployeeBusinessCard::class);
    }
}
