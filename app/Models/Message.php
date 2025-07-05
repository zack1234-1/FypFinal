<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['title', 'content','workspace_id'];

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
}
