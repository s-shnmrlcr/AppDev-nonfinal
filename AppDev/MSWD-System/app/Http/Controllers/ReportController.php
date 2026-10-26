<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $beneficiaryCount = Beneficiary::count();
        $verifiedCount = Beneficiary::where('verification_status', 'Verified')->count();
        $releasedPayouts = Payout::where('status', 'Released');
        // I-clone ang query aron dili maapil ang count sa sum ug magamit ang parehas nga filter. -shaine
        $releasedCount = (clone $releasedPayouts)->count();
        $releasedAmount = (clone $releasedPayouts)->sum('amount');
        // I-group sa database aron usa ra ka aggregate query ang ihatag, imbes mangita kada barangay. -shaine
        $beneficiaryCounts = DB::table('beneficiaries')
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');
        $releasedCounts = DB::table('payouts')
            ->join('beneficiaries', 'payouts.beneficiary_id', '=', 'beneficiaries.id')
            ->where('payouts.status', 'Released')
            ->selectRaw('beneficiaries.barangay_id, COUNT(*) as total')
            ->groupBy('beneficiaries.barangay_id')
            ->pluck('total', 'barangay_id');
        $barangays = DB::table('barangays')->orderBy('name')->get()
            ->map(fn ($barangay) => [
                'name' => $barangay->name,
                'beneficiaries' => $beneficiaryCounts[$barangay->id] ?? 0,
                'released' => $releasedCounts[$barangay->id] ?? 0,
            ]);

        return view('reports.index', compact(
            'barangays',
            'beneficiaryCount',
            'verifiedCount',
            'releasedCount',
            'releasedAmount'
        ));
    }
}
