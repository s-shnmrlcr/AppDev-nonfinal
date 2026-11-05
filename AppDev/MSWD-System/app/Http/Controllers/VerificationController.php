<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class VerificationController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return view('verification.index', compact('beneficiaries'));
    }

    public function review(Request $request): RedirectResponse
    {
        // Duha ra ka desisyon ang dawaton kay audit record kini sa manual nga visual review. -shaine
        $validated = $request->validate([
            'beneficiary_id' => ['required', 'integer', 'exists:beneficiaries,id'],
            'decision' => ['required', 'in:matched,not_matched'],
            'captured_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'id_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $beneficiary = Beneficiary::findOrFail($validated['beneficiary_id']);
        $reviewedAt = now();
        $capturedPhotoPath = $request->file('captured_photo')->store('identity-reviews', 'local');
        if ($capturedPhotoPath === false) {
            throw new RuntimeException('The captured claimant photo could not be stored.');
        }

        $idImagePath = $request->file('id_image')->store('identity-reviews', 'local');
        if ($idImagePath === false) {
            Storage::disk('local')->delete($capturedPhotoPath);
            throw new RuntimeException('The selected ID image could not be stored.');
        }

        try {
            DB::table('identity_verification_logs')->insert([
                'user_id' => $request->user()->id,
                'beneficiary_id' => $beneficiary->id,
                'applicant_name' => $beneficiary->full_name,
                'decision' => $validated['decision'],
                'captured_photo_path' => $capturedPhotoPath,
                'id_image_path' => $idImagePath,
                'created_at' => $reviewedAt,
                'updated_at' => $reviewedAt,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete([$capturedPhotoPath, $idImagePath]);
            throw $exception;
        }

        return redirect()
            ->route('verification.index')
            ->with('status', 'Identity review recorded in the audit log.');
    }

    public function showReview(int $log)
    {
        $review = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->first();

        abort_if($review === null, 404);

        return view('verification.review', compact('review'));
    }

    public function capturePhoto(int $log)
    {
        return $this->reviewImage($log, 'captured_photo_path');
    }

    public function idImage(int $log)
    {
        return $this->reviewImage($log, 'id_image_path');
    }

    private function reviewImage(int $log, string $column)
    {
        abort_unless(in_array($column, ['captured_photo_path', 'id_image_path'], true), 404);

        $path = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->value($column);

        abort_unless(
            is_string($path)
            && dirname($path) === 'identity-reviews'
            && Storage::disk('local')->exists($path),
            404
        );

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
