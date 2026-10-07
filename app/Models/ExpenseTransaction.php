<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_id',
        'account_id',
        'bank_id',
        'company_id',
        'staff_id',
        'supplier_id',
        'client_id',
        'hajj_umrah',
        'year',
        'amount',
        'payment_type',
        'transaction_type',
        'payee_type',
        'reference_no',
        'proof',
        'date',
        'description',
        'currency',
        'exchange_rate',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function allocatedCompanies()
    {
        return $this->hasMany(ExpenseTransactionCompany::class, 'expense_transaction_id');
    }
}
