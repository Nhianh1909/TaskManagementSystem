<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIChatMessage extends Model
{
    use HasFactory;

    protected $table = 'ai_chat_messages';

    protected $fillable = [
        'session_id',
        'role',
        'content',
        'suggestions',
    ];

    protected $casts = [
        'suggestions' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AIChatSession::class, 'session_id');
    }
}
