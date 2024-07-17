<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PersonalBusinessCard extends Model
{
    /**
     * Переопределение первичного ключа.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Тип первичного ключа.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Автоинкрементирование первичного ключа.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Устанавливаем атрибуты модели по умолчанию.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = self::generateUniqueId();
            }
        });
    }

    /**
     * Генерация уникального шестизначного идентификатора.
     *
     * @return string
     */
    public static function generateUniqueId(): string
    {
        $id = Str::random(6);

        while (self::where('id', $id)->exists()) {
            $id = Str::random(6);
        }

        return $id;
    }

    /**
     * Связь с моделью User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
