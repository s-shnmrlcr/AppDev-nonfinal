<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['label' => 'Total Beneficiaries', 'value' => '1,250', 'change' => '+8.2%', 'tone' => 'blue'],
            ['label' => 'Verified Beneficiaries', 'value' => '980', 'change' => '+5.4%', 'tone' => 'green'],
            ['label' => 'Pending Verification', 'value' => '180', 'change' => '14.4%', 'tone' => 'amber'],
            ['label' => 'Rejected', 'value' => '90', 'change' => '7.2%', 'tone' => 'red'],
            ['label' => 'Total Payouts', 'value' => '980', 'change' => '+12.1%', 'tone' => 'purple'],
            ['label' => 'Released', 'value' => '850', 'change' => '86.7%', 'tone' => 'green'],
            ['label' => 'Pending Release', 'value' => '130', 'change' => '13.3%', 'tone' => 'amber'],
            ['label' => 'Already Received', 'value' => '24', 'change' => 'Duplicate checks', 'tone' => 'red'],
        ];

        $activities = [
            ['name' => 'Juan Dela Cruz', 'action' => 'was verified', 'time' => '10:42 AM', 'type' => 'Verified'],
            ['name' => 'Maria Santos', 'action' => 'was added to the masterlist', 'time' => '10:18 AM', 'type' => 'Added'],
            ['name' => 'Pedro Garcia', 'action' => 'received payout PAY-2026-000184', 'time' => '09:55 AM', 'type' => 'Released'],
            ['name' => 'Ana Reyes', 'action' => 'was marked as duplicate', 'time' => '09:31 AM', 'type' => 'Duplicate'],
            ['name' => 'Staff Account', 'action' => 'uploaded a valid ID', 'time' => '09:12 AM', 'type' => 'Document'],
        ];

        return view('dashboard.index', compact('stats', 'activities'));
    }
}
