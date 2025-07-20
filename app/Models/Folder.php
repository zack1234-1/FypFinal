<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['name','workspace_id', 'creator_id'];

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
