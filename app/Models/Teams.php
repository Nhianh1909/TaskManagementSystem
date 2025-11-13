<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Sprints|null $activeSprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Epics> $epics
 * @property-read int|null $epics_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sprints> $sprints
 * @property-read int|null $sprints_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\TeamsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Teams extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Mối quan hệ nhiều-nhiều: Một Team có nhiều User (thành viên).
     */
    public function users()
    {
       return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
                    ->withPivot('roleInTeam') // Lấy cả thông tin vai trò trong team
                    ->withTimestamps();
    }

    /**
     * Mối quan hệ một-nhiều: Một Team có nhiều Sprint.
     */
    public function sprints()
    {
        return $this->hasMany(Sprints::class, 'team_id');
    }

    /**
     * Lấy sprint đang hoạt động của team.
     */
    public function activeSprint()
    {
        return $this->hasOne(Sprints::class, 'team_id')->where('is_active', true);
    }
    /**
     * Mối quan hệ một-nhiều: Một Team có nhiều Epic.
     */
    public function epics()
    {
        return $this->hasMany(Epics::class, 'team_id');
    }
}