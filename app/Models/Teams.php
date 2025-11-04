<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
