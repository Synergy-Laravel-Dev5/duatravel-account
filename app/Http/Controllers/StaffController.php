<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Payroll;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::with(['department', 'designation'])->latest()->get();
        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        return view('staff.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'status' => 'required|in:active,inactive'
        ]);
        Staff::create($request->all());
        return redirect()->route('staff.index')->with('success', 'Staff added successfully.');
    }

    public function edit(Staff $staff)
    {
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        return view('staff.edit', compact('staff', 'departments', 'designations'));
    }

    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'first_name' => 'required',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'status' => 'required|in:active,inactive'
        ]);
        $staff->update($request->all());
        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }

    public function ledgerFilter()
    {
        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();
        return view('staff.ledger_filter', compact('staffMembers'));
    }

    public function ledger(Request $request, $id = null)
    {
        $staffId = $id ?: $request->staff_id;
        if (!$staffId) {
            return redirect()->route('staff.ledger.filter')->with('error', 'Please select a staff member.');
        }

        $staff = Staff::with(['department', 'designation'])->findOrFail($staffId);
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $staffMembers = Staff::where('status', 'active')->orderBy('first_name')->get();

        // 1. Calculate Prior / Opening Balance before $fromDate (if provided)
        $openingBalance = 0;
        if ($fromDate) {
            $priorPayrolls = Payroll::where('staff_id', $staff->id)
                ->where(function ($q) use ($fromDate) {
                    $q->where('payment_date', '<', $fromDate)
                      ->orWhere(function ($q2) use ($fromDate) {
                          $q2->whereNull('payment_date')
                             ->whereDate('created_at', '<', $fromDate);
                      });
                })->sum('net_salary');

            $priorDebits = ExpenseTransaction::where('staff_id', $staff->id)
                ->whereDate('date', '<', $fromDate)
                ->where('transaction_type', 'debit')
                ->sum('amount');

            $priorCredits = ExpenseTransaction::where('staff_id', $staff->id)
                ->whereDate('date', '<', $fromDate)
                ->where('transaction_type', 'credit')
                ->sum('amount');

            $openingBalance = ($priorPayrolls + $priorCredits) - $priorDebits;
        }

        // 2. Query In-Period Payrolls (Credit to Staff)
        $payrollQuery = Payroll::where('staff_id', $staff->id);
        if ($fromDate && $toDate) {
            $payrollQuery->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('payment_date', [$fromDate, $toDate])
                  ->orWhere(function ($q2) use ($fromDate, $toDate) {
                      $q2->whereNull('payment_date')
                         ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
                  });
            });
        } elseif ($fromDate) {
            $payrollQuery->where(function ($q) use ($fromDate) {
                $q->where('payment_date', '>=', $fromDate)
                  ->orWhere(function ($q2) use ($fromDate) {
                      $q2->whereNull('payment_date')
                         ->whereDate('created_at', '>=', $fromDate);
                  });
            });
        } elseif ($toDate) {
            $payrollQuery->where(function ($q) use ($toDate) {
                $q->where('payment_date', '<=', $toDate)
                  ->orWhere(function ($q2) use ($toDate) {
                      $q2->whereNull('payment_date')
                         ->whereDate('created_at', '<=', $toDate);
                  });
            });
        }
        $payrolls = $payrollQuery->get();

        // 3. Query In-Period Expense Transactions (Disbursements / Advances / Deductions)
        $etQuery = ExpenseTransaction::where('staff_id', $staff->id);
        if ($fromDate && $toDate) {
            $etQuery->whereBetween('date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $etQuery->where('date', '>=', $fromDate);
        } elseif ($toDate) {
            $etQuery->where('date', '<=', $toDate);
        }
        $expenseTransactions = $etQuery->get();

        // 4. Combine into Chronological Ledger Entries
        $rawEntries = [];

        foreach ($payrolls as $p) {
            $date = $p->payment_date ?: ($p->created_at ? $p->created_at->format('Y-m-d') : date('Y-m-d'));
            $desc = "Monthly Salary: {$p->salary_month} {$p->salary_year}";
            $details = [];
            if ($p->basic_salary > 0) $details[] = "Basic: PKR " . number_format($p->basic_salary, 2);
            if ($p->allowance > 0) $details[] = "Allow: PKR " . number_format($p->allowance, 2);
            if ($p->bonus > 0) $details[] = "Bonus: PKR " . number_format($p->bonus, 2);
            if ($p->total_deductions > 0) $details[] = "Ded: PKR " . number_format($p->total_deductions, 2);
            if (!empty($details)) $desc .= ' (' . implode(', ', $details) . ')';
            if ($p->notes) $desc .= ' — ' . $p->notes;

            $rawEntries[] = [
                'date'           => $date,
                'created_at'     => $p->created_at ? $p->created_at->toDateTimeString() : $date . ' 00:00:00',
                'voucher_no'     => $p->payroll_number ?: ('PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
                'type'           => 'Payroll (Salary Credited)',
                'badge_class'    => 'bg-success text-white',
                'description'    => $desc,
                'payment_method' => $p->payment_method ?: 'Bank / Cash',
                'debit'          => 0,
                'credit'         => (float) $p->net_salary,
            ];
        }

        foreach ($expenseTransactions as $et) {
            $date = $et->date ? Carbon::parse($et->date)->format('Y-m-d') : ($et->created_at ? $et->created_at->format('Y-m-d') : date('Y-m-d'));
            $isDebit = ($et->transaction_type === 'debit');

            $badgeClass = $isDebit ? 'bg-danger text-white' : 'bg-info text-white';
            $typeLabel = $isDebit ? 'Disbursement / Advance Paid' : 'Staff Receipt / Adjustment';

            $rawEntries[] = [
                'date'           => $date,
                'created_at'     => $et->created_at ? $et->created_at->toDateTimeString() : $date . ' 00:00:00',
                'voucher_no'     => $et->reference_no ?: ('EXP-' . str_pad($et->id, 5, '0', STR_PAD_LEFT)),
                'type'           => $typeLabel,
                'badge_class'    => $badgeClass,
                'description'    => $et->description ?: ($isDebit ? 'Payment to Staff' : 'Adjustment from Staff'),
                'payment_method' => strtoupper($et->payment_type ?: 'Cash'),
                'debit'          => $isDebit ? (float) $et->amount : 0,
                'credit'         => !$isDebit ? (float) $et->amount : 0,
            ];
        }

        // Sort chronologically
        usort($rawEntries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return strcmp($a['created_at'], $b['created_at']);
            }
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance
        $runningBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($rawEntries as &$entry) {
            $totalDebit += $entry['debit'];
            $totalCredit += $entry['credit'];
            $runningBalance += ($entry['credit'] - $entry['debit']);
            $entry['balance'] = $runningBalance;
        }
        unset($entry);

        $finalBalance = $runningBalance;

        return view('staff.ledger', compact(
            'staff',
            'staffMembers',
            'fromDate',
            'toDate',
            'openingBalance',
            'rawEntries',
            'totalDebit',
            'totalCredit',
            'finalBalance'
        ));
    }
}
