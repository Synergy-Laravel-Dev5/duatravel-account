<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Bank;
use App\Models\ExpenseTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls with filters & statistics.
     */
    public function index(Request $request)
    {
        $query = Payroll::with(['staff.department', 'staff.designation', 'bank']);

        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year', date('Y'));
        $selectedStaff = $request->input('staff_id');
        $selectedStatus = $request->input('status');
        $selectedDept = $request->input('department_id');

        if ($selectedMonth) {
            $query->where('salary_month', $selectedMonth);
        }
        if ($selectedYear) {
            $query->where('salary_year', $selectedYear);
        }
        if ($selectedStaff) {
            $query->where('staff_id', $selectedStaff);
        }
        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }
        if ($selectedDept) {
            $query->whereHas('staff', function ($q) use ($selectedDept) {
                $q->where('department_id', $selectedDept);
            });
        }

        $payrolls = $query->latest('payment_date')->latest('id')->paginate(20)->withQueryString();

        // Calculate Overview Statistics
        $totalNetPaid = (clone $query)->where('status', 'paid')->sum('net_salary');
        $totalAdvanceDeducted = (clone $query)->sum('advance_salary');
        $totalAbsentDeduction = (clone $query)->sum('absent_deduction');
        $totalPayrollsCount = (clone $query)->count();

        $staffList = Staff::orderBy('first_name')->get();
        $departments = Department::where('status', 'active')->orderBy('name')->get();

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('payroll.index', compact(
            'payrolls',
            'staffList',
            'departments',
            'months',
            'selectedMonth',
            'selectedYear',
            'selectedStaff',
            'selectedStatus',
            'selectedDept',
            'totalNetPaid',
            'totalAdvanceDeducted',
            'totalAbsentDeduction',
            'totalPayrollsCount'
        ));
    }

    /**
     * Show form to create a new payroll.
     */
    public function create(Request $request)
    {
        $staffMembers = Staff::with(['department', 'designation'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $banks = Bank::orderBy('name')->get();

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $currentMonth = date('F');
        $currentYear = (int) date('Y');

        $preSelectedStaff = $request->input('staff_id');

        $lastId = Payroll::withTrashed()->max('id') ?? 0;
        $autoPayrollNumber = 'PR-' . date('Ym') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        return view('payroll.create', compact(
            'staffMembers',
            'banks',
            'months',
            'currentMonth',
            'currentYear',
            'preSelectedStaff',
            'autoPayrollNumber'
        ));
    }

    /**
     * AJAX endpoint: Retrieve staff base salary, month days, and auto-detect advance taken.
     */
    public function getStaffSalaryInfo(Request $request)
    {
        $staffId = $request->input('staff_id');
        $monthName = $request->input('month', date('F'));
        $year = (int) $request->input('year', date('Y'));

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Staff ID is required.'], 400);
        }

        $staff = Staff::with(['department', 'designation'])->find($staffId);
        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Staff member not found.'], 404);
        }

        // Determine month number and days in month
        try {
            $monthNum = Carbon::parse("1 {$monthName} {$year}")->month;
            $daysInMonth = Carbon::parse("1 {$monthName} {$year}")->daysInMonth;
        } catch (\Exception $e) {
            $monthNum = (int) date('m');
            $daysInMonth = 30;
        }

        // Auto-detect advances taken by this staff member in this specific month/year
        // Check expense_transactions where payee_type = 'staff' and staff_id = $staffId
        $advanceTransactionsQuery = ExpenseTransaction::where('staff_id', $staffId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->where('transaction_type', 'debit');

        // Exclude transactions that are already linked as final net salary disbursements to existing payrolls
        $linkedTxnIds = Payroll::whereNotNull('expense_transaction_id')->pluck('expense_transaction_id')->toArray();
        if (!empty($linkedTxnIds)) {
            $advanceTransactionsQuery->whereNotIn('id', $linkedTxnIds);
        }

        $advanceTransactions = $advanceTransactionsQuery->get();
        $totalAdvance = $advanceTransactions->sum('amount');

        $advanceList = $advanceTransactions->map(function ($tx) {
            return [
                'id'          => $tx->id,
                'date'        => Carbon::parse($tx->date)->format('d M, Y'),
                'amount'      => (float) $tx->amount,
                'description' => $tx->description ?: 'Advance payment',
                'ref_no'      => $tx->reference_no ?: ('EXP-' . $tx->id),
            ];
        });

        // Check if payroll already exists for this staff in this period
        $existingPayroll = Payroll::where('staff_id', $staffId)
            ->where('salary_month', $monthName)
            ->where('salary_year', $year)
            ->first();

        return response()->json([
            'success'            => true,
            'staff'              => [
                'id'           => $staff->id,
                'name'         => $staff->first_name . ' ' . $staff->last_name,
                'department'   => $staff->department->name ?? 'N/A',
                'designation'  => $staff->designation->name ?? 'N/A',
                'basic_salary' => (float) ($staff->salary ?: 0),
            ],
            'days_in_month'      => $daysInMonth,
            'advance_taken'      => (float) $totalAdvance,
            'advance_list'       => $advanceList,
            'already_generated'  => $existingPayroll ? true : false,
            'existing_payroll_id'=> $existingPayroll ? $existingPayroll->id : null,
        ]);
    }

    /**
     * Store newly created payroll.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payroll_number'   => 'nullable|string|max:50',
            'staff_id'         => 'required|exists:staff,id',
            'salary_month'     => 'required|string|max:20',
            'salary_year'      => 'required|integer|min:2000|max:2100',
            'payment_date'     => 'required|date',
            'total_days'       => 'required|numeric|min:1',
            'off_days'         => 'required|numeric|min:0',
            'working_days'     => 'required|numeric|min:0',
            'basic_salary'     => 'required|numeric|min:0',
            'per_day_salary'   => 'nullable|numeric|min:0',
            'absent_deduction' => 'nullable|numeric|min:0',
            'earned_salary'    => 'nullable|numeric|min:0',
            'allowance'        => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'gross_salary'     => 'nullable|numeric|min:0',
            'advance_salary'   => 'nullable|numeric|min:0',
            'other_deduction'  => 'nullable|numeric|min:0',
            'total_deductions' => 'nullable|numeric|min:0',
            'net_salary'       => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'bank_id'          => 'nullable|exists:banks,id',
            'status'           => 'required|in:paid,unpaid,draft',
            'notes'            => 'nullable|string',
        ]);

        // Duplicate check for staff member in the same month & year
        $already = Payroll::where('staff_id', $request->staff_id)
            ->where('salary_month', $request->salary_month)
            ->where('salary_year', $request->salary_year)
            ->first();

        if ($already) {
            return back()->withInput()->withErrors([
                'error' => "Payroll for this staff member for {$request->salary_month} {$request->salary_year} has already been generated (Voucher: {$already->payroll_number})."
            ]);
        }

        DB::beginTransaction();
        try {
            $lastId = Payroll::withTrashed()->max('id') ?? 0;
            $payrollNo = $request->payroll_number ?: ('PR-' . date('Ym') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT));

            $staff = Staff::findOrFail($request->staff_id);

            // Mathematical verification
            $totalDays = (float) $request->total_days;
            $offDays = (float) $request->off_days;
            $workingDays = max(0, $totalDays - $offDays);
            $basicSalary = (float) $request->basic_salary;
            $perDay = ($totalDays > 0) ? round($basicSalary / $totalDays, 2) : 0;
            $absentDeduction = round($perDay * $offDays, 2);
            $earnedSalary = max(0, $basicSalary - $absentDeduction);

            $allowance = (float) ($request->allowance ?: 0);
            $bonus = (float) ($request->bonus ?: 0);
            $grossSalary = $earnedSalary + $allowance + $bonus;

            $advanceSalary = (float) ($request->advance_salary ?: 0);
            $otherDeduction = (float) ($request->other_deduction ?: 0);
            $totalDeductions = $absentDeduction + $advanceSalary + $otherDeduction;

            $netSalary = max(0, $grossSalary - $advanceSalary - $otherDeduction);

            $payroll = Payroll::create([
                'payroll_number'   => $payrollNo,
                'staff_id'         => $staff->id,
                'salary_month'     => $request->salary_month,
                'salary_year'      => $request->salary_year,
                'payment_date'     => Carbon::parse($request->payment_date)->format('Y-m-d'),
                'total_days'       => $totalDays,
                'off_days'         => $offDays,
                'working_days'     => $workingDays,
                'basic_salary'     => $basicSalary,
                'per_day_salary'   => $perDay,
                'absent_deduction' => $absentDeduction,
                'earned_salary'    => $earnedSalary,
                'allowance'        => $allowance,
                'bonus'            => $bonus,
                'gross_salary'     => $grossSalary,
                'advance_salary'   => $advanceSalary,
                'other_deduction'  => $otherDeduction,
                'total_deductions' => $totalDeductions,
                'net_salary'       => $netSalary,
                'payment_method'   => $request->payment_method ?: 'cash',
                'bank_id'          => $request->bank_id ?: null,
                'status'           => $request->status ?: 'paid',
                'notes'            => $request->notes,
            ]);

            // If marked as 'paid' and auto_create_transaction is checked, record in ExpenseTransaction
            $shouldRecordTxn = $request->boolean('auto_create_transaction', false);
            if ($payroll->status === 'paid' && $payroll->net_salary > 0 && $shouldRecordTxn) {
                $pkg = session('dashboard_package', 'hajj');
                $expenseTxn = ExpenseTransaction::create([
                    'bank_id'          => $payroll->bank_id ?: null,
                    'expense_id'       => null,
                    'supplier_id'      => null,
                    'client_id'        => null,
                    'company_id'       => null,
                    'staff_id'         => $payroll->staff_id,
                    'hajj_umrah'       => $pkg,
                    'year'             => $payroll->salary_year,
                    'amount'           => $payroll->net_salary,
                    'payment_type'     => $payroll->payment_method ?: 'cash',
                    'transaction_type' => 'debit',
                    'payee_type'       => 'staff',
                    'currency'         => 'PKR',
                    'exchange_rate'    => 1,
                    'reference_no'     => $payroll->payroll_number,
                    'proof'            => null,
                    'date'             => $payroll->payment_date->format('Y-m-d'),
                    'description'      => "Salary for {$payroll->salary_month} {$payroll->salary_year} | Staff: {$staff->first_name} {$staff->last_name} | Days: {$payroll->working_days}/{$payroll->total_days} (Off: {$payroll->off_days}) | Adv Deducted: PKR " . number_format($payroll->advance_salary, 2),
                ]);

                $payroll->update(['expense_transaction_id' => $expenseTxn->id]);
            }

            if (function_exists('logUserActivity')) {
                logUserActivity('Payroll Created', "Staff: {$staff->first_name} {$staff->last_name} | Month: {$payroll->salary_month} {$payroll->salary_year} | Net Pay: {$payroll->net_salary}", $payroll->id, 'Payroll');
            }

            DB::commit();

            return redirect()->route('payroll.show', $payroll->id)
                ->with('success', "Payroll generated successfully for {$staff->first_name} {$staff->last_name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to generate payroll: ' . $e->getMessage()]);
        }
    }

    /**
     * Show payroll details and printable salary pay slip.
     */
    public function show($id)
    {
        $payroll = Payroll::with(['staff.department', 'staff.designation', 'bank', 'expenseTransaction'])->findOrFail($id);
        return view('payroll.show', compact('payroll'));
    }

    /**
     * Show form to edit an existing payroll.
     */
    public function edit($id)
    {
        $payroll = Payroll::with(['staff.department', 'staff.designation'])->findOrFail($id);
        $banks = Bank::orderBy('name')->get();

        return view('payroll.edit', compact('payroll', 'banks'));
    }

    /**
     * Update an existing payroll.
     */
    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        $request->validate([
            'payment_date'     => 'required|date',
            'total_days'       => 'required|numeric|min:1',
            'off_days'         => 'required|numeric|min:0',
            'working_days'     => 'required|numeric|min:0',
            'basic_salary'     => 'required|numeric|min:0',
            'allowance'        => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'advance_salary'   => 'nullable|numeric|min:0',
            'other_deduction'  => 'nullable|numeric|min:0',
            'net_salary'       => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
            'bank_id'          => 'nullable|exists:banks,id',
            'status'           => 'required|in:paid,unpaid,draft',
            'notes'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $totalDays = (float) $request->total_days;
            $offDays = (float) $request->off_days;
            $workingDays = max(0, $totalDays - $offDays);
            $basicSalary = (float) $request->basic_salary;
            $perDay = ($totalDays > 0) ? round($basicSalary / $totalDays, 2) : 0;
            $absentDeduction = round($perDay * $offDays, 2);
            $earnedSalary = max(0, $basicSalary - $absentDeduction);

            $allowance = (float) ($request->allowance ?: 0);
            $bonus = (float) ($request->bonus ?: 0);
            $grossSalary = $earnedSalary + $allowance + $bonus;

            $advanceSalary = (float) ($request->advance_salary ?: 0);
            $otherDeduction = (float) ($request->other_deduction ?: 0);
            $totalDeductions = $absentDeduction + $advanceSalary + $otherDeduction;

            $netSalary = max(0, $grossSalary - $advanceSalary - $otherDeduction);

            $payroll->update([
                'payment_date'     => Carbon::parse($request->payment_date)->format('Y-m-d'),
                'total_days'       => $totalDays,
                'off_days'         => $offDays,
                'working_days'     => $workingDays,
                'basic_salary'     => $basicSalary,
                'per_day_salary'   => $perDay,
                'absent_deduction' => $absentDeduction,
                'earned_salary'    => $earnedSalary,
                'allowance'        => $allowance,
                'bonus'            => $bonus,
                'gross_salary'     => $grossSalary,
                'advance_salary'   => $advanceSalary,
                'other_deduction'  => $otherDeduction,
                'total_deductions' => $totalDeductions,
                'net_salary'       => $netSalary,
                'payment_method'   => $request->payment_method ?: 'cash',
                'bank_id'          => $request->bank_id ?: null,
                'status'           => $request->status,
                'notes'            => $request->notes,
            ]);

            // Sync or create ExpenseTransaction if auto_create_transaction is checked
            $shouldRecordTxn = $request->boolean('auto_create_transaction', false);
            if ($payroll->status === 'paid' && $payroll->net_salary > 0 && $shouldRecordTxn) {
                if ($payroll->expense_transaction_id && $payroll->expenseTransaction) {
                    $payroll->expenseTransaction->update([
                        'bank_id'      => $payroll->bank_id ?: null,
                        'amount'       => $payroll->net_salary,
                        'payment_type' => $payroll->payment_method ?: 'cash',
                        'date'         => $payroll->payment_date->format('Y-m-d'),
                        'description'  => "Salary for {$payroll->salary_month} {$payroll->salary_year} | Staff: {$payroll->staff->first_name} {$payroll->staff->last_name} | Days: {$payroll->working_days}/{$payroll->total_days} (Off: {$payroll->off_days}) | Adv Deducted: PKR " . number_format($payroll->advance_salary, 2),
                    ]);
                } else {
                    $pkg = session('dashboard_package', 'hajj');
                    $expenseTxn = ExpenseTransaction::create([
                        'bank_id'          => $payroll->bank_id ?: null,
                        'expense_id'       => null,
                        'supplier_id'      => null,
                        'client_id'        => null,
                        'company_id'       => null,
                        'staff_id'         => $payroll->staff_id,
                        'hajj_umrah'       => $pkg,
                        'year'             => $payroll->salary_year,
                        'amount'           => $payroll->net_salary,
                        'payment_type'     => $payroll->payment_method ?: 'cash',
                        'transaction_type' => 'debit',
                        'payee_type'       => 'staff',
                        'currency'         => 'PKR',
                        'exchange_rate'    => 1,
                        'reference_no'     => $payroll->payroll_number,
                        'proof'            => null,
                        'date'             => $payroll->payment_date->format('Y-m-d'),
                        'description'      => "Salary for {$payroll->salary_month} {$payroll->salary_year} | Staff: {$payroll->staff->first_name} {$payroll->staff->last_name} | Days: {$payroll->working_days}/{$payroll->total_days} (Off: {$payroll->off_days}) | Adv Deducted: PKR " . number_format($payroll->advance_salary, 2),
                    ]);
                    $payroll->update(['expense_transaction_id' => $expenseTxn->id]);
                }
            } elseif ((!$shouldRecordTxn || $payroll->status !== 'paid') && $payroll->expense_transaction_id) {
                // If unlinked or reverted to unpaid, delete the disbursement record
                if ($payroll->expenseTransaction) {
                    $payroll->expenseTransaction->delete();
                }
                $payroll->update(['expense_transaction_id' => null]);
            }

            if (function_exists('logUserActivity')) {
                logUserActivity('Payroll Updated', "ID: {$payroll->id} | Net Pay: {$payroll->net_salary}", $payroll->id, 'Payroll');
            }

            DB::commit();

            return redirect()->route('payroll.show', $payroll->id)
                ->with('success', 'Payroll updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update payroll: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete payroll record.
     */
    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($payroll->expense_transaction_id && $payroll->expenseTransaction) {
                $payroll->expenseTransaction->delete();
            }

            if (function_exists('logUserActivity')) {
                logUserActivity('Payroll Deleted', "Voucher: {$payroll->payroll_number} | Staff: {$payroll->staff_id}", $payroll->id, 'Payroll');
            }

            $payroll->delete();
            DB::commit();

            return redirect()->route('payroll.index')->with('success', 'Payroll deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete payroll: ' . $e->getMessage()]);
        }
    }
}
