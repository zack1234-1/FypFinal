<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkspace extends Model
{
    protected $table = 'user_workspace'; 

    protected $fillable = [
        'admin_id',
        'workspace_id',
        'user_id',
    ];

    public $timestamps = false; 
}
