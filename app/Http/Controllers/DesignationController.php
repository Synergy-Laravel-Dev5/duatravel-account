<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Department;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('department')->latest()->get();
        return view('designation.index', compact('designations'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        return view('designation.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate(['department_id' => 'required|exists:departments,id', 'name' => 'required', 'status' => 'required|in:active,inactive']);
        Designation::create($request->all());
        return redirect()->route('designation.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        $departments = Department::where('status', 'active')->get();
        return view('designation.edit', compact('designation', 'departments'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate(['department_id' => 'required|exists:departments,id', 'name' => 'required', 'status' => 'required|in:active,inactive']);
        $designation->update($request->all());
        return redirect()->route('designation.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();
        return redirect()->route('designation.index')->with('success', 'Designation deleted successfully.');
    }
}
