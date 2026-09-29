<?php

use App\Models\User;
use App\Models\CompanyLogin;

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [

        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'company' => [
            'driver' => 'session',
            'provider' => 'company_logins',
        ],

    ],

    'providers' => [

        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        'company_logins' => [
            'driver' => 'eloquent',
            'model' => CompanyLogin::class,
        ],

    ],

    'passwords' => [

        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

    ],

    'password_timeout' => 10800,

];
