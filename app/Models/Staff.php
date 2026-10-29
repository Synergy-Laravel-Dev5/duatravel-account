<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function expenseTransactions()
    {
        return $this->hasMany(ExpenseTransaction::class, 'staff_id');
    }
}
