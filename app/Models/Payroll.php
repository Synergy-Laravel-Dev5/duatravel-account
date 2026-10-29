<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date'      => 'date',
        'total_days'        => 'float',
        'off_days'          => 'float',
        'working_days'      => 'float',
        'basic_salary'      => 'float',
        'per_day_salary'    => 'float',
        'absent_deduction'  => 'float',
        'earned_salary'     => 'float',
        'allowance'         => 'float',
        'bonus'             => 'float',
        'gross_salary'      => 'float',
        'advance_salary'    => 'float',
        'other_deduction'   => 'float',
        'total_deductions'  => 'float',
        'net_salary'        => 'float',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class)->withTrashed();
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function expenseTransaction()
    {
        return $this->belongsTo(ExpenseTransaction::class, 'expense_transaction_id');
    }
}
