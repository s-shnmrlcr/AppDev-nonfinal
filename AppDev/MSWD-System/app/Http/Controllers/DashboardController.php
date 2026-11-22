<?php
// -shaine: Builds live dashboard metrics, charts, and recent activity.

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // -shaine: Aggregates live beneficiary, payout, barangay, and activity data.
    public function index()
    {
        $beneficiaryStatusCounts = Beneficiary::query()
            ->selectRaw('verification_status, COUNT(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status')
            ->all();
        $payoutStatusCounts = Payout::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // -shaine: Scales each status bar against the largest count in its chart.
        $buildStatusChart = static function (array $slices): array {
            $total = array_sum(array_column($slices, 'count'));
            $maxCount = max(array_column($slices, 'count') ?: [0]);

            foreach ($slices as $index => $slice) {
                $slices[$index]['barHeight'] = $maxCount > 0 && $slice['count'] > 0
                    ? max(6, (int) round($slice['count'] / $maxCount * 100))
                    : 0;
            }

            return ['total' => $total, 'slices' => $slices];
        };

        $beneficiaryStatusColors = [
            'Verified' => '#806bae',
            'Pending' => '#c6a46b',
            'Needs Correction' => '#c8866f',
            'Rejected' => '#b77483',
        ];
        $beneficiarySlices = [];

        foreach ($beneficiaryStatusColors as $status => $color) {
            $beneficiarySlices[] = [
                'label' => $status,
                'count' => (int) ($beneficiaryStatusCounts[$status] ?? 0),
                'color' => $color,
            ];
        }

        // -shaine: Preserve unexpected database statuses with a neutral fallback color.
        foreach ($beneficiaryStatusCounts as $status => $count) {
            if (array_key_exists($status, $beneficiaryStatusColors)) {
                continue;
            }

            $beneficiarySlices[] = [
                'label' => $status ?: 'Unspecified',
                'count' => (int) $count,
                'color' => '#8791a0',
            ];
        }

        $beneficiaryChart = $buildStatusChart($beneficiarySlices);
        $payoutStatusColors = [
            'Released' => '#c87963',
            'Partially Released' => '#c9a15f',
            'Pending' => '#a8b0bd',
        ];
        $payoutSlices = [];

        foreach ($payoutStatusColors as $status => $color) {
            $payoutSlices[] = [
                'label' => $status,
                'count' => (int) ($payoutStatusCounts[$status] ?? 0),
                'color' => $color,
            ];
        }

        // -shaine: Preserve unexpected payout statuses with a neutral fallback color.
        foreach ($payoutStatusCounts as $status => $count) {
            if (array_key_exists($status, $payoutStatusColors)) {
                continue;
            }

            $payoutSlices[] = [
                'label' => $status ?: 'Unspecified',
                'count' => (int) $count,
                'color' => '#8791a0',
            ];
        }

        $payoutChart = $buildStatusChart($payoutSlices);

        $barangayCounts = DB::table('barangays')
            ->leftJoin('beneficiaries', 'beneficiaries.barangay_id', '=', 'barangays.id')
            ->select('barangays.name')
            ->selectRaw('COUNT(beneficiaries.id) as total')
            ->groupBy('barangays.id', 'barangays.name')
            ->orderByDesc('total')
            ->orderBy('barangays.name')
            ->limit(8)
            ->get()
            ->filter(fn (object $barangay) => (int) $barangay->total > 0)
            ->values();
        $maxBarangayCount = (int) ($barangayCounts->max('total') ?? 0);
        // -shaine: Expresses each barangay count relative to the busiest barangay.
        $barangayCounts->transform(function (object $barangay) use ($maxBarangayCount) {
            $barangay->percentage = $maxBarangayCount > 0
                ? (int) round((int) $barangay->total / $maxBarangayCount * 100)
                : 0;

            return $barangay;
        });

        $stats = [
            ['label' => 'Total Beneficiaries', 'value' => $beneficiaryChart['total'], 'tone' => 'blue'],
            ['label' => 'Verified Beneficiaries', 'value' => (int) ($beneficiaryStatusCounts['Verified'] ?? 0), 'tone' => 'green'],
            ['label' => 'Pending Verification', 'value' => (int) ($beneficiaryStatusCounts['Pending'] ?? 0), 'tone' => 'amber'],
            ['label' => 'Payouts Released', 'value' => (int) ($payoutStatusCounts['Released'] ?? 0), 'tone' => 'purple'],
        ];
        $recentActivities = TransactionLog::query()
            ->latest('created_at')
            ->latest('id')
            ->limit(8)
            ->get(['action', 'subject_type', 'subject_id', 'created_at']);

        return view('dashboard.index', compact(
            'stats',
            'beneficiaryChart',
            'payoutChart',
            'barangayCounts',
            'recentActivities'
        ));
    }
}
