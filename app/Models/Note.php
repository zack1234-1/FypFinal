<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'color',
        'creator_id',        
        'workspace_id',
        'admin_id',
        'status_id'
    ];
}
