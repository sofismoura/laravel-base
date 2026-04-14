<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chirp extends Model
{
    protected $fillable = [
        'user_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class);
    }
}