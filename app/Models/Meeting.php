<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $table = 'meeting'; 

    protected $fillable = [
        'topic',
        'start_time',
        'duration',
        'zoom_join_url',
        'zoom_start_url',
        'recording',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    // Relationship: Meeting created by a user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
