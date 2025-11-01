<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetrospectiveItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'retrospective_id',
        'user_id',
        'content',
        'type', //e.g., 'went_well', 'to_improve', 'action_item'
    ];
    //lấy buổi họp retrospective mà item này thuộc về
    public function retrospective()
    {
        return $this->belongsTo(Retrospective::class, 'retrospective_id');
    }
    //lấy các các user đã tạo item này
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

