<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['label' => 'Total Beneficiaries', 'value' => Beneficiary::count(), 'tone' => 'blue'],
            ['label' => 'Verified Beneficiaries', 'value' => Beneficiary::where('verification_status', 'Verified')->count(), 'tone' => 'green'],
            ['label' => 'Pending Verification', 'value' => Beneficiary::where('verification_status', 'Pending')->count(), 'tone' => 'amber'],
            ['label' => 'Payouts Released', 'value' => Payout::where('status', 'Released')->count(), 'tone' => 'purple'],
        ];

        return view('dashboard.index', compact('stats'));
    }
}
