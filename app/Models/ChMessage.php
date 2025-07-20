<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ChMessage extends Model
{
    use HasFactory;

    protected $table = 'ch_messages';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'workspace_id', 'from_id', 'to_id', 'body', 'attachment', 'seen','file_name','reply_file_name'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate UUID for primary key
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function groupSender()
    {
        return $this->belongsTo(User::class, 'messager_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_id');
    }
}
