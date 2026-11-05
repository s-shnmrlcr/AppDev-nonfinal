<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}
