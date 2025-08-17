<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprints extends Model
{
    use HasFactory;

    /**
     * Các trường được phép gán hàng loạt (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'goal',
        'start_date',
        'end_date',
        'is_active',
        'team_id', // <-- Thêm cột này vào
    ];
}
