<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Tasks extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class, 'assigned_to');
    }
    // public function UserSprint(){
    //     return $this->hasManyThrough(
    //         Sprint::class,

    //     )
    // }
}
