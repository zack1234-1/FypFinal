<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    protected $table = 'cards';

    protected $fillable = [
        'title',
        'description',
        'creator_id',        
        'workspace_id',
        'admin_id',
        'status_id'
    ];
}
