<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::query()
            ->with(['beneficiary.barangay', 'program'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Payout $payout) => [
                'ref' => $payout->reference,
                'beneficiary' => $payout->beneficiary->full_name,
                'barangay' => $payout->beneficiary->barangay->name,
                'program' => $payout->program->name,
                'amount' => '₱'.number_format((float) $payout->amount, 2),
                'date' => $payout->released_at?->format('M j, Y') ?? '—',
                'status' => $payout->status,
            ]);

        return view('payouts.index', compact('payouts'));
    }

    public function export(): StreamedResponse
    {
        $payouts = Payout::query()
            ->with(['beneficiary.barangay', 'program'])
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($payouts): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'reference', 'beneficiary', 'barangay', 'program', 'amount', 'status', 'released_at',
            ], escape: '');

            // I-apil ang related records gikan sa preloaded relations aron walay dugang query matag payout. -shaine
            foreach ($payouts as $payout) {
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $payout->reference,
                    $payout->beneficiary->full_name,
                    $payout->beneficiary->barangay->name,
                    $payout->program->name,
                    $payout->amount,
                    $payout->status,
                    $payout->released_at?->format('Y-m-d H:i:s'),
                ]), escape: '');
            }

            fclose($output);
        }, 'payout-releases.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvValue(mixed $value): mixed
    {
        // Likayi nga ma-interpret isip spreadsheet formula ang mga text nga magsugod sa delikadong simbolo. -shaine
        if (! is_string($value) || preg_match('/^[\s]*[=+\-@]/', $value) !== 1) {
            return $value;
        }

        return "'".$value;
    }
}
