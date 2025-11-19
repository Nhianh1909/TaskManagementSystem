<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $sprint_id
 * @property int $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RetrospectiveItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Sprints $sprint
 * @property-read \App\Models\Teams $team
 * @method static \Database\Factories\RetrospectiveFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective whereSprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrospective whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Retrospective extends Model
{
    use HasFactory;
    protected $fillable = [
        'sprint_id',
        'team_id',
        'is_locked',
    ];
    protected $casts = [
        'is_locked' => 'boolean',
    ];
    //lấy chỉ 1 sprint của cuộc họp này
    public function sprint()
    {
        return $this->belongsTo(Sprints::class, 'sprint_id');
    }
    //lấy chỉ 1 team của cuộc họp này
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
    //lấy tất cả các 'tag' item trong cuộc họp này
    public function items()
    {
        return $this->hasMany(RetrospectiveItem::class, 'retrospective_id');
    }
}
