<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasksComments extends Model
{
    use HasFactory;

    // Bảng mặc định không phải số nhiều chuẩn
    protected $table = 'tasks_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(TasksComments::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(TasksComments::class, 'parent_id');
    }
}
