<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseTransactionCompany extends Model
{
    protected $table = 'expense_transaction_companies';

    protected $guarded = ['id'];

    public function expenseTransaction()
    {
        return $this->belongsTo(ExpenseTransaction::class, 'expense_transaction_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
