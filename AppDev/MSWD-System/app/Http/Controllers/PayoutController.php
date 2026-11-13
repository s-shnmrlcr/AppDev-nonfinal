<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\Program;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutController extends Controller
{
    // Display payout records with an optional program filter
    public function index(Request $request)
    {
        // Validate the selected program ID
        $validated = $request->validate([
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
        ]);

        // Get all payout records with their related beneficiary and program
        $payouts = Payout::query()
            ->with(['beneficiary.barangay', 'program'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Payout $payout) => [
                'id' => $payout->id,
                'ref' => $payout->reference,
                'beneficiary' => $payout->beneficiary->full_name,
                'barangay' => $payout->beneficiary->barangay->name,
                'program' => $payout->program->program_name,
                'amount' => '₱'.number_format((float) $payout->amount, 2),
                'date' => $payout->released_at?->format('M j, Y') ?? '—',
                'status' => $payout->status,
            ]);

        // Get available programs for the program filter
        $programs = Program::orderBy('program_name')->get();

        return view('payouts.index', compact('payouts', 'programs'));
    }

    // Export payout records, respecting the selected program filter
    public function export(Request $request): StreamedResponse
    {
        // Validate the selected program ID
        $validated = $request->validate([
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
        ]);

        $payouts = Payout::query()
            ->with(['beneficiary.barangay', 'program'])
            ->when(
                $validated['program_id'] ?? null,
                fn ($query, $programId) => $query->where('program_id', $programId)
            )
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($payouts): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'reference',
                'beneficiary',
                'barangay',
                'program',
                'amount',
                'status',
                'released_at',
            ], escape: '');

            // Include related records from the preloaded relationships
            foreach ($payouts as $payout) {
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $payout->reference,
                    $payout->beneficiary->full_name,
                    $payout->beneficiary->barangay->name,
                    $payout->program->program_name,
                    $payout->amount,
                    $payout->status,
                    $payout->released_at?->format('Y-m-d H:i:s'),
                ]), escape: '');
            }

            fclose($output);
        }, 'payout-releases.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // Protect exported CSV values from spreadsheet formula injection
    private function safeCsvValue(mixed $value): mixed
    {
        if (! is_string($value) || preg_match('/^[\s]*[=+\-@]/', $value) !== 1) {
            return $value;
        }

        return "'" . $value;
    }
}
