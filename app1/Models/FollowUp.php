<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public static function getStatuses(): array
    {
        return [
            'pending'                 => 'Pending',
            'done'                    => 'Done',
            'need further follow up'  => 'Need Further Follow Up',
        ];
    }

    protected $casts = [
        'create_date' => 'datetime',
        'taken_at' => 'datetime',
        'due_date' => 'datetime',
    ];
}
