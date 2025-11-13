<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $retrospective_id
 * @property int $user_id
 * @property string $content
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Retrospective $retrospective
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\RetrospectiveItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereRetrospectiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RetrospectiveItem whereUserId($value)
 * @mixin \Eloquent
 */
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

