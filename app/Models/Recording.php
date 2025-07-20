<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'recording_blob',
        'user_id',
        'workspace_id'
    ];
}
