<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyBusinessCard extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['logo', 'name', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::random(5);
        });
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function websites()
    {
        $this->hasMany(CompanyWebsite::class);
    }

    public function employees()
    {
        $this->hasMany(EmployeeBusinessCard::class);
    }
}
