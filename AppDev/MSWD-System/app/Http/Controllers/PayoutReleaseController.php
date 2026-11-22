<?php
// -shaine: Records payout releases and maintains their status history.

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\PayoutRelease;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PayoutReleaseController extends Controller
{
    // -shaine: Loads payout details, release totals, and the staff release history.
    public function index(Payout $payout)
    {
        $payout->load([
            'beneficiary.barangay',
            'program',
            'releases',
        ]);

        $releaseStaffNames = TransactionLog::query()
            ->where('subject_type', 'PayoutRelease')
            ->whereIn('subject_id', $payout->releases->modelKeys())
            ->latest('id')
            ->get(['subject_id', 'staff_name'])
            ->unique('subject_id')
            ->pluck('staff_name', 'subject_id');

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
            'remainingBalance',
            'releaseStaffNames'
        ));

    }

    // -shaine: Records a release without allowing concurrent requests to exceed the balance.
    public function store(Request $request, Payout $payout)
    {
        // -shaine: Require a staff attribution and a positive release amount.
        $validated = $request->validate([
            'staff_name' => ['required', 'string', 'max:255'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ]);

        // -shaine: Lock, validate, and save the release in one transaction to prevent overpayment.
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
            $release = PayoutRelease::create([
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

            TransactionLog::record(
                $validated['staff_name'],
                'Payout release recorded',
                'PayoutRelease',
                $release->id,
                $release->reference.' — '.number_format($releaseAmount, 2)
            );
        });

        return redirect()
            ->route('payout-releases.index', $payout->id)
            ->with('status', 'Assistance release successfully recorded.');
    }
}
