<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(): StreamedResponse
    {
        $barangays = DB::table('barangays')->orderBy('name')->get();
        $beneficiaryCounts = DB::table('beneficiaries')
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');
        $verifiedCounts = DB::table('beneficiaries')
            ->where('verification_status', 'Verified')
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');
        $releasedCounts = DB::table('payouts')
            ->join('beneficiaries', 'payouts.beneficiary_id', '=', 'beneficiaries.id')
            ->where('payouts.status', 'Released')
            ->selectRaw('beneficiaries.barangay_id, COUNT(*) as total')
            ->groupBy('beneficiaries.barangay_id')
            ->pluck('total', 'barangay_id');

        return response()->streamDownload(function () use ($barangays, $beneficiaryCounts, $verifiedCounts, $releasedCounts): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new \RuntimeException('The report CSV output stream could not be opened.');
            }

            fputcsv($output, ['barangay', 'beneficiaries', 'verified', 'released', 'pending', 'release_rate'], escape: '');

            foreach ($barangays as $barangay) {
                $beneficiaries = (int) ($beneficiaryCounts[$barangay->id] ?? 0);
                $verified = (int) ($verifiedCounts[$barangay->id] ?? 0);
                $released = (int) ($releasedCounts[$barangay->id] ?? 0);
                $pending = max($beneficiaries - $released, 0);
                $rate = $beneficiaries > 0 ? round(($released / $beneficiaries) * 100) : 0;

                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $barangay->name,
                    $beneficiaries,
                    $verified,
                    $released,
                    $pending,
                    $rate.'%',
                ]), escape: '');
            }

            fclose($output);
        }, 'report-masterlist.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (! is_string($value) || preg_match('/^[\s]*[=+\-@]/', $value) !== 1) {
            return $value;
        }

        return "'".$value;
    }
}
