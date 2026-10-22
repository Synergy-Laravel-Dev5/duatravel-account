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

if (!function_exists('isCompanyUser')) {
    function isCompanyUser()
    {
        if (Auth::guard('company')->check()) {
            return true;
        }
        if (Auth::guard('web')->check() && !empty(Auth::guard('web')->user()->company_id)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('getAuthCompanyId')) {
    function getAuthCompanyId()
    {
        if (Auth::guard('company')->check()) {
            return Auth::guard('company')->user()->company_id ?? Auth::guard('company')->id();
        }
        if (Auth::guard('web')->check() && !empty(Auth::guard('web')->user()->company_id)) {
            return Auth::guard('web')->user()->company_id;
        }
        return null;
    }
}

if (!function_exists('getAuthCompany')) {
    function getAuthCompany()
    {
        if (Auth::guard('company')->check()) {
            return Auth::guard('company')->user()->company ?? Auth::guard('company')->user();
        }
        if (Auth::guard('web')->check() && !empty(Auth::guard('web')->user()->company_id)) {
            return Auth::guard('web')->user()->company;
        }
        return null;
    }
}

if (!function_exists('currentSeason')) {
    function currentSeason()
    {
        return \App\Models\Season::current();
    }
}

if (!function_exists('currentSeasonName')) {
    function currentSeasonName()
    {
        $cur = currentSeason();
        if ($cur) {
            return $cur->name;
        }
        $pkg = session('dashboard_package', 'hajj');
        $yr  = session('dashboard_year', date('Y'));
        return ucfirst($pkg) . ' ' . $yr;
    }
}

if (!function_exists('currentSeasonType')) {
    function currentSeasonType()
    {
        return session('dashboard_package', 'hajj');
    }
}

if (!function_exists('currentSeasonYear')) {
    function currentSeasonYear()
    {
        return (int) session('dashboard_year', date('Y'));
    }
}

if (!function_exists('allSeasonsList')) {
    function allSeasonsList($type = null)
    {
        $q = \App\Models\Season::orderByDesc('year')->orderBy('season_type');
        if ($type) {
            $q->where('season_type', $type);
        }
        return $q->get();
    }
}
