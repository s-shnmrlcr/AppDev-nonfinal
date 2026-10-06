<?php

namespace App\Http\Controllers;

class PayoutController extends Controller
{
    private function payouts(): array
    {
        return [
            ['ref' => 'PAY-2026-000184', 'beneficiary' => 'Pedro Garcia', 'barangay' => 'Barangay 03', 'program' => 'AYUDA 2026', 'amount' => '₱5,000', 'date' => 'Oct 06, 2026', 'status' => 'Released'],
            ['ref' => 'PAY-2026-000183', 'beneficiary' => 'Carla Navarro', 'barangay' => 'Barangay 07', 'program' => 'AYUDA 2026', 'amount' => '₱5,000', 'date' => 'Oct 06, 2026', 'status' => 'Released'],
            ['ref' => 'PAY-2026-000182', 'beneficiary' => 'Juan Dela Cruz', 'barangay' => 'Barangay 01', 'program' => 'AYUDA 2026', 'amount' => '₱5,000', 'date' => 'Pending', 'status' => 'Ready for Release'],
            ['ref' => 'PAY-2026-000181', 'beneficiary' => 'Ana Reyes', 'barangay' => 'Barangay 04', 'program' => 'AYUDA 2026', 'amount' => '₱5,000', 'date' => '—', 'status' => 'Blocked'],
        ];
    }

    public function index()
    {
        $payouts = $this->payouts();

        return view('payouts.index', compact('payouts'));
    }

    public function show(string $payout)
    {
        $payout = collect($this->payouts())->firstWhere('ref', $payout);
        abort_if($payout === null, 404);

        return view('payouts.show', compact('payout'));
    }
}
