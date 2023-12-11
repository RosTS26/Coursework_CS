<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Friend extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'friends';
    protected $guarded = ['id'];

    // Ссылка на родительскую таблицу
    public function User() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
