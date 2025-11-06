<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status',
    ];

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
