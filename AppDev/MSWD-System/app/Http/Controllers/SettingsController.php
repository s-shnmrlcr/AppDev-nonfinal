<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'system_name' => 'MSWDO Kabacan',
            'email_domain' => config('mswd.authorized_email_domain', '@dswd.gov.ph'),
            'barangays' => DB::table('barangays')->count(),
        ];

        return view('settings.index', compact('settings'));
    }
}
