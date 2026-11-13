<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Beneficiary;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::query()->orderByDesc('created_at')->get()
            ->map(fn (Program $program) => [
                'id' => 'PRG-'.str_pad((string) $program->id, 6, '0', STR_PAD_LEFT),
                'record_id' => $program->id,
                'name' => $program->program_name,
                'description' => $program->description ?: 'No description provided',
                'amount' => '₱'.number_format((float) $program->budget, 2),
                'start' => $program->start_date?->format('M j, Y') ?? 'No start date',
                'status' => $program->status,
                'end' => $program->end_date?->format('M j, Y') ?? 'No end date',
            ]);

        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function edit(Program $program)
    {
        return view('programs.create', compact('program'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_name' => ['required', 'string', 'max:255', 'unique:programs,program_name'],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:Draft,Active'],
        ]);

        Program::create($validated);

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program created.');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'program_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('programs', 'program_name')->ignore($program->id),
            ],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:Draft,Active'],
        ]);

        $program->update($validated);

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program updated.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        if ($program->payouts()->exists()) {
            return redirect()
                ->route('programs.index')
                ->withErrors([
                    'program' => 'This program cannot be deleted because payout records are linked to it.',
                ]);
        }

        $program->delete();

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program deleted.');
    }

    // Display verified beneficiaries who can be assigned to this program
    public function manageBeneficiaries(Program $program)
    {
        // Get beneficiaries already assigned to this program
        $assignedPayouts = $program->payouts()
            ->with('beneficiary')
            ->orderByDesc('created_at')
            ->get();

        // Get their IDs so they won't appear in the available list
        $assignedIds = $assignedPayouts->pluck('beneficiary_id');

        // Only verified beneficiaries who are not yet assigned
        $availableBeneficiaries = Beneficiary::where('verification_status', 'Verified')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('programs.beneficiaries', compact(
            'program',
            'assignedPayouts',
            'availableBeneficiaries'
        ));
    }


    // Assign selected beneficiaries to the program
    public function assignBeneficiaries(Request $request, Program $program)
    {
        // Validate the fixed assistance amount and selected beneficiaries
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'beneficiary_ids' => ['required', 'array', 'min:1'],
            'beneficiary_ids.*' => ['required', 'integer', 'distinct', 'exists:beneficiaries,id'],
        ]);

        $beneficiaryIds = $validated['beneficiary_ids'];
        $amount = (float) $validated['amount'];

        // Make sure every selected beneficiary is verified
        $verifiedCount = Beneficiary::whereIn('id', $beneficiaryIds)
            ->where('verification_status', 'Verified')
            ->count();

        if ($verifiedCount !== count($beneficiaryIds)) {
            return back()->withErrors([
                'beneficiary_ids' => 'Only verified beneficiaries can be assigned.'
            ])->withInput();
        }

        // Prevent duplicate assignments
        $alreadyAssigned = Payout::where('program_id', $program->id)
            ->whereIn('beneficiary_id', $beneficiaryIds)
            ->exists();

        if ($alreadyAssigned) {
            return back()->withErrors([
                'beneficiary_ids' => 'One or more selected beneficiaries are already assigned to this program.'
            ])->withInput();
        }

        // Check whether the remaining program budget can cover the assignments
        $alreadyAllocated = (float) $program->payouts()->sum('amount');
        $newAllocation = $amount * count($beneficiaryIds);

        if (($alreadyAllocated + $newAllocation) > (float) $program->budget) {
            return back()->withErrors([
                'amount' => 'The total assistance amount exceeds the remaining program budget.'
            ])->withInput();
        }

        // Create all payout records together
        DB::transaction(function () use ($program, $beneficiaryIds, $amount) {

            foreach ($beneficiaryIds as $beneficiaryId) {

                // Generate a unique payout reference
                do {
                    $reference = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                } while (Payout::where('reference', $reference)->exists());

                Payout::create([
                    'reference' => $reference,
                    'beneficiary_id' => $beneficiaryId,
                    'program_id' => $program->id,
                    'amount' => $amount,
                    'status' => 'Pending',
                    'released_at' => null,
                ]);
            }
        });

        return redirect()
            ->route('programs.beneficiaries', $program->id)
            ->with('status', count($beneficiaryIds) . ' beneficiary payout(s) successfully assigned.');
    }
}
