<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    public function index()
    {
        $mealPlans = MealPlan::latest()->get();
        return view('meal_plans.index', compact('mealPlans'));
    }

    public function create()
    {
        return view('meal_plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:meal_plans,code',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $mealPlan = MealPlan::create([
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Meal plan created successfully!',
                'data'    => $mealPlan,
            ]);
        }

        return redirect()->route('meal-plans.index')->with('success', 'Meal plan created successfully!');
    }

    public function edit($id)
    {
        $mealPlan = MealPlan::findOrFail($id);
        return view('meal_plans.edit', compact('mealPlan'));
    }

    public function update(Request $request, $id)
    {
        $mealPlan = MealPlan::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:meal_plans,code,' . $id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $mealPlan->update([
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        return redirect()->route('meal-plans.index')->with('success', 'Meal plan updated successfully!');
    }

    public function destroy($id)
    {
        $mealPlan = MealPlan::findOrFail($id);
        $mealPlan->delete();

        return redirect()->route('meal-plans.index')->with('success', 'Meal plan deleted successfully!');
    }

    public function getJson()
    {
        return response()->json(MealPlan::where('status', 'active')->get());
    }
}
