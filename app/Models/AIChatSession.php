<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIChatSession extends Model
{
    use HasFactory;

    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'user_id',
        'team_id',
        'epic_id',
        'sprint_id',
        'workflow_stage',
        'context',
        'status',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function messages()
    {
        return $this->hasMany(AIChatMessage::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    public function epic()
    {
        return $this->belongsTo(Epics::class, 'epic_id');
    }

    public function sprint()
    {
        return $this->belongsTo(Sprints::class, 'sprint_id');
    }
}
