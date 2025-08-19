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
}
