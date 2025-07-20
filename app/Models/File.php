<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'filename',
        'mime_type',
        'content',
        'size',
        'folder_id',
        'creator_id',
        'workspace_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

}
