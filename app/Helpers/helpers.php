<?php

use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

if (!function_exists('logUserActivity')) {

    function logUserActivity($activityType, $details = null, $entityId = null, $entityType = null)
    {
        UserActivity::create([
            'user_id'       => Auth::id(),
            'activity_type' => $activityType,
            'details'       => $details,
            'entity_id'     => $entityId,
            'entity_type'   => $entityType,
            'ip_address'    => request()->ip(),
        ]);
    }
}