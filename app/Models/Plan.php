<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'max_team_members',
        'max_projects',
        'plan_type',
        'modules',
    ];

    protected $casts = [
        'modules' => 'json',
    ];
    public function getlink()
    {
        return str(route('plans.edit', ['id' => $this->id]));
    }
    public function getresult()
    {

        return substr($this->name, 0, 100);
    }
}
