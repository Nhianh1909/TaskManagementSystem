<?php
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Thêm 'role' vào fillable để có thể cập nhật qua Seeder
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mối quan hệ nhiều-nhiều: Một User có thể thuộc về nhiều Team.
     */
    public function teams()
    {
        return $this->belongsToMany(Teams::class, 'team_members', 'user_id', 'team_id')
                    ->withPivot('roleInTeam') // Lấy cả thông tin vai trò trong team
                    ->withTimestamps();
    }

    /**
     * Lấy team hiện tại của user.
     * Giả định mỗi user chỉ thuộc về một team chính tại một thời điểm.
     */
    public function team()
    {
        return $this->teams()->first();
    }

    /**
     * Lấy các task được giao cho user này.
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'assigned_to');
    }
}
