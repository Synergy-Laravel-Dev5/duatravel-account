<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'entity_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'entity_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'entity_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'entity_id');
    }

    public function getEntityNameAttribute()
    {
        if ($this->entity_type === 'staff') {
            $st = Staff::find($this->entity_id);
            return $st ? ($st->first_name . ' ' . $st->last_name) : null;
        } elseif ($this->entity_type === 'client') {
            $cl = Client::find($this->entity_id);
            return $cl ? $cl->name : null;
        } elseif ($this->entity_type === 'supplier') {
            $sp = Supplier::find($this->entity_id);
            return $sp ? $sp->name : null;
        } elseif ($this->entity_type === 'bank') {
            $bk = Bank::find($this->entity_id);
            return $bk ? $bk->name : null;
        }
        return null;
    }
}
