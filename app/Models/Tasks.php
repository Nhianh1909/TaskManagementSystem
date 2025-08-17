<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Tasks extends Model
{
    use HasFactory;

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'priority',
        'storyPoints',
        'assigned_to',
        'created_by',
        'sprint_id',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
