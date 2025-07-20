<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'priority',
        'description',
        'creator_id',
        'creator_type',
        'completed',
        'workspace_id',
        'admin_id',
        'start_date',
        'end_date',
        'user_id',
        'status'
    ];

    protected $casts = [
        'user_id' => 'array', 
    ];

    public function creator()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'todo_user', 'todo_id', 'user_id');
    }

}