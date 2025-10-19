<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprints extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'goal',
        'start_date',
        'end_date',
        'is_active',
        'team_id',
        'status',
    ];

    /**
     * Mối quan hệ một-nhiều: Một Sprint có nhiều Task.
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'sprint_id');
    }

    /**
     * Mối quan hệ một-một: Sprint này thuộc về Team nào.
     */
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
}
