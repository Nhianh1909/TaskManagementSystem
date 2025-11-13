<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $team_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Teams $team
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tasks> $userStories
 * @property-read int|null $user_stories_count
 * @method static \Database\Factories\EpicsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Epics whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
