<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function index()
    {
        return view('verification.index');
    }

    public function review(Request $request): RedirectResponse
    {
        // Duha ra ka desisyon ang dawaton kay audit record kini sa manual nga visual review. -shaine
        $validated = $request->validate([
            'decision' => ['required', 'in:matched,not_matched'],
        ]);

        DB::table('identity_verification_logs')->insert([
            'user_id' => $request->user()->id,
            'decision' => $validated['decision'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('verification.index')
            ->with('status', 'Identity review recorded in the audit log.');
    }
}
