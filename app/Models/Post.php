<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'title',
        'body',
        'user_external_id',
        'tags',
        'reactions', // Поле уже было в fillable
    ];

    /**
     * Приведение типов атрибутов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
        'reactions' => 'array', // <--- ДОБАВИТЬ ЭТУ СТРОКУ
    ];

 
}
