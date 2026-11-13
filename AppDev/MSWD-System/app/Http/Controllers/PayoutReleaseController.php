<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\PayoutRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PayoutReleaseController extends Controller
{
    // Display payout details and release history
    public function index(Payout $payout)
    {
        $payout->load([
            'beneficiary.barangay',
            'program',
            'releases',
        ]);

        // Calculate the total amount successfully released
        $releasedTotal = $payout->releases
            ->where('status', 'Released')
            ->sum('amount');

        // Calculate the remaining assistance balance
        $remainingBalance = max(
            0,
            (float) $payout->amount - (float) $releasedTotal
        );

        return view('payout-releases.index', compact(
            'payout',
            'releasedTotal',
            'remainingBalance'
        ));

    }

    // Record a new assistance release
    public function store(Request $request, Payout $payout)
    {
        // Validate the release amount
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ]);

        DB::transaction(function () use ($payout, $validated) {

            // Lock the payout while checking its remaining balance
            $lockedPayout = Payout::whereKey($payout->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Calculate the total amount already released
            $releasedTotal = (float) $lockedPayout->releases()
                ->where('status', 'Released')
                ->sum('amount');

            // Calculate the remaining assistance amount
            $remaining = round(
                (float) $lockedPayout->amount - $releasedTotal,
                2
            );

            $releaseAmount = round((float) $validated['amount'], 2);

            // Prevent releasing more than the assigned assistance
            if ($releaseAmount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'The release amount exceeds the remaining assistance balance of ₱'
                        . number_format($remaining, 2) . '.',
                ]);
            }

            // Generate a unique release reference automatically
            do {
                $reference = 'REL-' . now()->format('Ymd') . '-'
                    . Str::upper(Str::random(6));

            } while (PayoutRelease::where('reference', $reference)->exists());

            // Create the release record
            PayoutRelease::create([
                'payout_id' => $lockedPayout->id,
                'reference' => $reference,
                'amount' => $releaseAmount,
                'released_at' => now(),
                'released_by' => auth()->id(),
                'status' => 'Released',
            ]);

            // Update the overall payout status
            $newReleasedTotal = round(
                $releasedTotal + $releaseAmount,
                2
            );

            if ($newReleasedTotal >= (float) $lockedPayout->amount) {
                $lockedPayout->status = 'Released';
            } else {
                $lockedPayout->status = 'Partially Released';
            }

            $lockedPayout->save();
        });

        return redirect()
            ->route('payout-releases.index', $payout->id)
            ->with('status', 'Assistance release successfully recorded.');
    }
}
