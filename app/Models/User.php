<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Создание таблицы друзей
            $user->friend()->create([
                'user_id' => $user->id,
                'friends' => json_encode([]),
                'sent_app' => json_encode([]),
                'incoming_app' => json_encode([]),
            ]);
        });
    }

    // Ссылки на ДОЧЕРНИЕ таблицы
    // Один к одному
    public function Friend() {
        return $this->hasOne(Friend::class, 'user_id', 'id');
    }

    // Связь один ко многим
    public function Chat() {
        return $this->hasMany(Chat::class, 'user_id', 'id');
    }
}
