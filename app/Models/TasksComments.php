<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read TasksComments|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TasksComments> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\Tasks $task
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TasksCommentsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TasksComments whereUserId($value)
 * @mixin \Eloquent
 */
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
