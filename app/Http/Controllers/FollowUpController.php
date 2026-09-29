<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FollowUpController extends Controller
{
    public function store(Request $request, $leadId)
    {
        $lead = Lead::findOrFail($leadId);

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'create_date' => 'required',
            'reason' => 'nullable|string|max:255',
        ]);

        $data['lead_id'] = $lead->id;
        $data['create_date'] = Carbon::parse($data['create_date']);
        $data['status'] = 'pending';

        FollowUp::create($data);

        return redirect()
            ->back()
            ->with('success', 'Follow-up created successfully');
    }

    public function take(Request $request, $id)
    {
        $followUp = FollowUp::findOrFail($id);

        $data = $request->validate([
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,done,need further follow up',
            'taken_at' => 'nullable',
            'due_date' => 'nullable',
        ]);

        $data['taken_at'] = $data['taken_at'] ? Carbon::parse($data['taken_at']) : now();
        $data['due_date'] = $data['due_date'] ? Carbon::parse($data['due_date']) : null;

        $followUp->update($data);

        return redirect()
            ->back()
            ->with('success', 'Follow-up updated successfully');
    }
}
