<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_id',
        'hajj_umrah',        
        'year',              
        'amount',
        'payment_type',
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
}
