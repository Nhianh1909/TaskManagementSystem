<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order_index',
        'is_done',
        'color_class',
        'team_id',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Relationship: TaskStatus có nhiều Tasks
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'status_id');
    }

    /**
     * Relationship: TaskStatus thuộc về Team
     */
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
}
