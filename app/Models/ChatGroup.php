<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ChatGroup extends Model
{
    use HasFactory;

    protected $table = 'chat_groups';

    protected $fillable = [
    'name',
    'created_by',
    'user_ids',
    'workspace_id',
    ];

    protected $casts = [
        'user_ids' => 'array', 
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return User::whereIn('id', $this->user_ids)->get();
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function messages()
    {
        return $this->hasMany(ChMessage::class, 'group_id')->orderBy('created_at');
    }

}
