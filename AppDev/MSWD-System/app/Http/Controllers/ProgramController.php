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
                'name' => $program->name,
                'type' => $program->type,
                'amount' => '₱'.number_format((float) $program->amount, 2),
                'coverage' => $program->coverage,
                'status' => $program->status,
                'end' => $program->end_date?->format('M j, Y') ?? 'No end date',
                'db_id' => $program->id,
            ]);

        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:programs,name'],
            'type' => ['required', 'in:Financial Assistance,Emergency Aid,In-kind Assistance,Other'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'coverage' => ['required', 'string', 'max:255'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:Draft,Active'],
        ]);

        Program::create($validated);

        return redirect()
            ->route('programs.index')
            ->with('status', 'Payout program created.');
    }

    public function edit(Program $program)
{
    return view('programs.edit', compact('program'));
}

public function update(Request $request, Program $program): RedirectResponse
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', Rule::unique('programs', 'name')->ignore($program->id)],
        'type' => ['required', 'in:Financial Assistance,Emergency Aid,In-kind Assistance,Other'],
        'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        'coverage' => ['required', 'string', 'max:255'],
        'end_date' => ['nullable', 'date'],
        'status' => ['required', 'in:Draft,Active'],
    ]);

    $program->update($validated);

    return redirect()->route('programs.index')->with('status', 'Program updated.');
}

public function destroy(Program $program): RedirectResponse
{
    $program->delete();

    return redirect()->route('programs.index')->with('status', 'Program deleted.');
}
}
