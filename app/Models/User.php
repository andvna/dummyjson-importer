<?php

namespace App\Models;

 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable // implements MustVerifyEmail // Если нужна верификация
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',      // <-- Добавлено
        'first_name',       // <-- Добавлено
        'last_name',        // <-- Добавлено
        'username',         // <-- Добавлено
        'phone',            // <-- Добавлено
        'image_url',        // <-- Добавлено
        'name',             // Оставляем (он станет nullable после миграции)
        'email',
        'password',         // Оставляем (он станет nullable после миграции)
        'email_verified_at',// Стандартное поле, можно оставить
 
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Атрибуты, которые должны быть приведены к типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
 
    ];

 
 
 
 
 
 
 
 

 
 
 
 
 
}
