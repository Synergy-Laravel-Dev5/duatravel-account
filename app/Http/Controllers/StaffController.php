<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Designation;

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
}
