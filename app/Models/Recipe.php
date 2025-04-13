<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'ingredients',
        'instructions',
        'prep_time_minutes',
        'cook_time_minutes',
        'servings',
        'difficulty',
        'cuisine',
        'calories_per_serving',
        'rating',
        'image_url',
 
 
    ];

    /**
     * Приведение типов атрибутов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ingredients' => 'array', // Автоматически кодировать/декодировать JSON для ingredients
        'instructions' => 'array', // Автоматически кодировать/декодировать JSON для instructions
 
        'rating' => 'decimal:2',
    ];
}
