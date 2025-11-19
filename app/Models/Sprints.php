<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $team_id
 * @property string $name
 * @property string|null $goal
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string $status
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tasks> $tasks
 * @property-read int|null $tasks_count
 * @property-read \App\Models\Teams $team
 * @method static \Database\Factories\SprintsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sprints whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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
