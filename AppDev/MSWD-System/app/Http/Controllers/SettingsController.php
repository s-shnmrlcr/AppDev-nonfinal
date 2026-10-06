<?php

namespace App\Http\Controllers;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'system_name' => 'DSWD Payout Verification System',
            'email_domain' => config('mswd.authorized_email_domain', '@dswd.gov.ph'),
            'barangays' => 17,
            'prototype_mode' => true,
        ];

        return view('settings.index', compact('settings'));
    }
}
