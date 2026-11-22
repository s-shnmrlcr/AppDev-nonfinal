<?php
// -shaine: Records identity checks and serves their protected review images.

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\TransactionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class VerificationController extends Controller
{
    // -shaine: Lists registered applicants for an in-person identity review.
    public function index()
    {
        $beneficiaries = Beneficiary::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return view('verification.index', compact('beneficiaries'));
    }

    // -shaine: Validates review inputs, saves protected images, and records the decision.
    public function review(Request $request): RedirectResponse
    {
        // Duha ra ka desisyon ang dawaton kay audit record kini sa manual nga visual review. -shaine
        $validated = $request->validate([
            'staff_name' => ['required', 'string', 'max:255'],
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

        // -shaine: Store images before the database transaction so failures can clean up both files.
        try {
            // -shaine: Save the review record and its audit entry as one atomic operation.
            DB::transaction(function () use (
                $request,
                $beneficiary,
                $validated,
                $capturedPhotoPath,
                $idImagePath,
                $reviewedAt
            ): void {
                $logId = DB::table('identity_verification_logs')->insertGetId([
                    'user_id' => $request->user()->id,
                    'beneficiary_id' => $beneficiary->id,
                    'applicant_name' => $beneficiary->full_name,
                    'decision' => $validated['decision'],
                    'captured_photo_path' => $capturedPhotoPath,
                    'id_image_path' => $idImagePath,
                    'created_at' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                ]);

                TransactionLog::record(
                    $validated['staff_name'],
                    'Identity review recorded',
                    'IdentityVerificationLog',
                    $logId,
                    'Beneficiary #'.$beneficiary->id.' — '.$validated['decision']
                );
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete([$capturedPhotoPath, $idImagePath]);
            throw $exception;
        }

        return redirect()
            ->route('verification.index')
            ->with('status', 'Identity review recorded in the audit log.');
    }

    // -shaine: Loads a saved identity review and its transaction staff name.
    public function showReview(int $log)
    {
        $review = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->first();

        abort_if($review === null, 404);
        $staffName = TransactionLog::query()
            ->where('subject_type', 'IdentityVerificationLog')
            ->where('subject_id', $review->id)
            ->where('action', 'Identity review recorded')
            ->value('staff_name');

        return view('verification.review', compact('review', 'staffName'));
    }

    // -shaine: Streams the captured claimant photo after access and path checks.
    public function capturePhoto(int $log)
    {
        return $this->reviewImage($log, 'captured_photo_path');
    }

    // -shaine: Streams the selected ID image after access and path checks.
    public function idImage(int $log)
    {
        return $this->reviewImage($log, 'id_image_path');
    }

    // -shaine: Serves only approved image columns stored in the private review folder.
    private function reviewImage(int $log, string $column)
    {
        // -shaine: Whitelist column names so request input cannot select arbitrary database fields.
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
