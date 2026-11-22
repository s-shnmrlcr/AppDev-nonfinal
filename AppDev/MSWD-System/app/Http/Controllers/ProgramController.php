<?php
// -shaine: Manages assistance programs and beneficiary assignments.

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;
use App\Models\Program;
use App\Models\TransactionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    // -shaine: Returns payout programs formatted for the program list.
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

    // -shaine: Opens a blank form for creating a payout program.
    public function create()
    {
        return view('programs.create');
    }

    // -shaine: Opens the shared program form with the selected record.
    public function edit(Program $program)
    {
        return view('programs.create', compact('program'));
    }

    // -shaine: Validates and records a new program with staff attribution.
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_name' => ['required', 'string', 'max:255'],
            'program_name' => ['required', 'string', 'max:255', 'unique:programs,program_name'],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:Draft,Active'],
        ]);

        $staffName = $validated['staff_name'];
        unset($validated['staff_name']);

        // -shaine: Detect required legacy columns so older program schemas still accept current form data.
        $requiredColumns = collect(Schema::getColumns('programs'))
            ->filter(fn (array $column) => ! $column['nullable'] && $column['default'] === null)
            ->pluck('name')
            ->all();

        // -shaine: Map current form values to legacy columns only when the schema requires them.
        $legacyValues = [
            'name' => $validated['program_name'],
            'type' => 'Assistance',
            'amount' => $validated['budget'],
            'coverage' => $validated['description'] ?? 'Not specified',
        ];

        foreach ($legacyValues as $column => $value) {
            if (in_array($column, $requiredColumns, true)) {
                $validated[$column] = $value;
            }
        }

        $program = DB::transaction(function () use ($validated, $staffName): Program {
            $program = Program::create($validated);

            TransactionLog::record(
                $staffName,
                'Program created',
                'Program',
                $program->id,
                $program->program_name
            );

            return $program;
        });

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program created.');
    }

    // -shaine: Updates program fields and records the responsible staff account.
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

        // -shaine: Keep program changes and their audit record in one database transaction.
        DB::transaction(function () use ($request, $program, $validated): void {
            $program->update($validated);

            TransactionLog::record(
                $request->user()->name,
                'Program updated',
                'Program',
                $program->id,
                $program->program_name
            );
        });

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program updated.');
    }

    // -shaine: Deletes only unassigned programs and records the action.
    public function destroy(Request $request, Program $program): RedirectResponse
    {
        if ($program->payouts()->exists()) {
            return redirect()
                ->route('programs.index')
                ->withErrors([
                    'program' => 'This program cannot be deleted because payout records are linked to it.',
                ]);
        }

        // -shaine: Keep program deletion and its audit event atomic.
        DB::transaction(function () use ($request, $program): void {
            $name = $program->program_name;
            $id = $program->id;
            $program->delete();

            TransactionLog::record(
                $request->user()->name,
                'Program deleted',
                'Program',
                $id,
                $name
            );
        });

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program deleted.');
    }

    // -shaine: Shows available verified beneficiaries and assignment/release staff history.
    public function manageBeneficiaries(Program $program)
    {
        // Get beneficiaries already assigned to this program
        $assignedPayouts = $program->payouts()
            ->with(['beneficiary', 'releases.releasedBy'])
            ->orderByDesc('created_at')
            ->get();
        $releaseStaffNames = TransactionLog::query()
            ->where('subject_type', 'PayoutRelease')
            ->whereIn(
                'subject_id',
                $assignedPayouts->flatMap(fn (Payout $payout) => $payout->releases->modelKeys())->unique()
            )
            ->latest('id')
            ->get(['subject_id', 'staff_name'])
            ->unique('subject_id')
            ->keyBy('subject_id');
        $releaseStaffByPayout = $assignedPayouts->mapWithKeys(fn (Payout $payout) => [
            $payout->id => $payout->releases
                ->map(fn ($release) => $releaseStaffNames->get($release->id)?->staff_name
                    ?? $release->releasedBy?->name)
                ->filter()
                ->unique()
                ->values(),
        ]);
        $assignmentStaffNames = TransactionLog::query()
            ->where('subject_type', 'Payout')
            ->where('action', 'Beneficiary assigned to program')
            ->whereIn('subject_id', $assignedPayouts->modelKeys())
            ->latest('id')
            ->get(['subject_id', 'staff_name'])
            ->unique('subject_id')
            ->pluck('staff_name', 'subject_id');

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
            'availableBeneficiaries',
            'releaseStaffByPayout',
            'assignmentStaffNames'
        ));
    }


    // -shaine: Validates eligibility, duplicate assignments, and budget before creating payouts.
    public function assignBeneficiaries(Request $request, Program $program)
    {
        // Validate the fixed assistance amount and selected beneficiaries
        $validated = $request->validate([
            'staff_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'beneficiary_ids' => ['required', 'array', 'min:1'],
            'beneficiary_ids.*' => ['required', 'integer', 'distinct', 'exists:beneficiaries,id'],
        ]);

        $staffName = $validated['staff_name'];
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

        // -shaine: Keep payout rows and their audit records all-or-nothing.
        DB::transaction(function () use ($program, $beneficiaryIds, $amount, $staffName) {

            foreach ($beneficiaryIds as $beneficiaryId) {

                // Generate a unique payout reference
                do {
                    $reference = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                } while (Payout::where('reference', $reference)->exists());

                $payout = Payout::create([
                    'reference' => $reference,
                    'beneficiary_id' => $beneficiaryId,
                    'program_id' => $program->id,
                    'amount' => $amount,
                    'status' => 'Pending',
                    'released_at' => null,
                ]);

                TransactionLog::record(
                    $staffName,
                    'Beneficiary assigned to program',
                    'Payout',
                    $payout->id,
                    'Program '.$program->program_name.'; beneficiary #'.$beneficiaryId
                );
            }

            TransactionLog::record(
                $staffName,
                'Beneficiaries assigned to program',
                'Program',
                $program->id,
                count($beneficiaryIds).' payout(s) created at '.number_format($amount, 2).' each.'
            );
        });

        return redirect()
            ->route('programs.beneficiaries', $program->id)
            ->with('status', count($beneficiaryIds) . ' beneficiary payout(s) successfully assigned.');
    }
}
