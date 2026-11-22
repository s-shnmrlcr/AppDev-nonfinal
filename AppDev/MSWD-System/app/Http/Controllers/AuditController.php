<?php
// -shaine: Loads and manages the system audit log records.

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditController extends Controller
{
    // -shaine: Loads identity review and transaction records for the audit table.
    public function index()
    {
        $logs = DB::table('identity_verification_logs')
            ->join('users', 'identity_verification_logs.user_id', '=', 'users.id')
            ->leftJoin('beneficiaries', 'identity_verification_logs.beneficiary_id', '=', 'beneficiaries.id')
            ->select([
                'identity_verification_logs.id',
                'identity_verification_logs.beneficiary_id',
                'identity_verification_logs.applicant_name',
                'identity_verification_logs.decision',
                'identity_verification_logs.created_at',
                'users.name as account_name',
                'users.email',
                'beneficiaries.id as current_beneficiary_id',
            ])
            ->latest('identity_verification_logs.created_at')
            ->get();

        $transactionLogs = TransactionLog::with('user')
            ->latest()
            ->limit(500)
            ->get();

        return view('audit.index', compact('logs', 'transactionLogs'));
    }

    // -shaine: Deletes an identity review and its private images without changing the beneficiary.
    public function destroy(Request $request, int $log): RedirectResponse
    {
        $review = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->first(['id', 'captured_photo_path', 'id_image_path']);

        abort_if($review === null, 404);

        // -shaine: Record the deletion audit event in the same transaction as the review removal.
        DB::transaction(function () use ($request, $review): void {
            DB::table('identity_verification_logs')
                ->where('id', $review->id)
                ->delete();

            TransactionLog::record(
                $request->user()->name,
                'Identity review audit log deleted',
                'IdentityVerificationLog',
                $review->id
            );
        });

        $imagePaths = array_filter([
            $review->captured_photo_path,
            $review->id_image_path,
        ], fn ($path) => is_string($path) && dirname($path) === 'identity-reviews');

        if ($imagePaths !== []) {
            Storage::disk('local')->delete($imagePaths);
        }

        return redirect()
            ->route('audit-logs.index')
            ->with('status', 'Identity audit log deleted. The beneficiary record was not changed.');
    }
}
