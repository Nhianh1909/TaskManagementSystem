<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $sprint_id
 * @property int|null $sprint_order
 * @property int $created_by
 * @property int|null $assigned_to
 * @property string $title
 * @property string|null $description
 * @property string $priority
 * @property int|null $storyPoints
 * @property string $status
 * @property int|null $order_index
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $epic_id
 * @property int|null $epic_order
 * @property int|null $parent_id
 * @property-read \App\Models\User|null $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TasksComments> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\Epics|null $epic
 * @property-read Tasks|null $parent
 * @property-read \App\Models\Sprints|null $sprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tasks> $subTasks
 * @property-read int|null $sub_tasks_count
 * @method static \Database\Factories\TasksFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereEpicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereEpicOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereOrderIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereSprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereSprintOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereStoryPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tasks extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'storyPoints',
        'assigned_to',
        'created_by',
        'sprint_id',
        'epic_id',
        'parent_id',
        'status_id',    // 🔥 MỚI: Thay cho 'status'
        'order_index',
        'completed_at', // 🔥 MỚI: Để vẽ Burndown Chart chính xác
    ];
    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    /**
     * Mối quan hệ một-một: Task này được giao cho User nào.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Mối quan hệ một-một: Task này được tạo bởi User nào.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mối quan hệ một-một: Task này thuộc về Sprint nào.
     */
    public function sprint()
    {
        return $this->belongsTo(Sprints::class, 'sprint_id');
    }

    /**
     * Mối quan hệ một-một: Task này thuộc về Epic nào.
     */
    public function epic()
    {
        return $this->belongsTo(Epics::class, 'epic_id');
    }

    /**
     * Mối quan hệ một-một: subTask này có parent Task nào (nếu là sub-task).
     */
    public function parent()
    {
        return $this->belongsTo(Tasks::class, 'parent_id');
    }
    /**
     * Mối quan hệ một-nhiều: Task này có thể có nhiều sub-tasks nếu là user story.
     */
    public function subTasks()
    {
        return $this->hasMany(Tasks::class, 'parent_id');
    }

    /**
     * Mối quan hệ một-nhiều: Task có nhiều comments.
     */
    public function comments()
    {
        return $this->hasMany(TasksComments::class, 'task_id');
    }
}
