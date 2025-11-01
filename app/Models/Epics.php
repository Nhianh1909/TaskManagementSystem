<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epics extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id',
        'title',
        'description',
    ];
    //mỗi epic thuộc về một team
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
    //lấy tất cả user story mà chưa có phân rã thành các sub_task thuộc về epic này
    public function userStories()
    {
        return $this->hasMany(Tasks::class, 'epic_id')->whereNull('parent_id');
    }
}
