<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Retrospective extends Model
{
    use HasFactory;
    protected $fillable = [
        'sprint_id',
        'team_id',
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
