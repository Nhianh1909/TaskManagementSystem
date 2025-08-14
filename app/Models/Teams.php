<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;

    public function users(){
       return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
       ->withPivot('team_id', 'roleInTeam');
    }
}

