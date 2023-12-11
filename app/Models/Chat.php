<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'chats';
    protected $guarded = ['id'];

    public function User() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
