<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use SoftDeletes;

    protected $table = 'room_types';

    protected $fillable = [
        'name',
        'code',
        'capacity',
        'description',
        'status',
    ];

    /**
     * Scope for active room types.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
