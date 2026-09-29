<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index()
    {
        $userActivities = UserActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.activity_show', compact('userActivities'));
    }
}
