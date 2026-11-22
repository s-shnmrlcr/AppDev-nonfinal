<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditController extends Controller
{
    public function index()
    {
        $logs = DB::table('identity_verification_logs')
            ->join('users', 'identity_verification_logs.user_id', '=', 'users.id')
            ->leftJoin('beneficiaries', 'identity_verification_logs.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('transaction_logs', function ($join) {
                $join->on('transaction_logs.subject_id', '=', 'identity_verification_logs.id')
                    ->where('transaction_logs.subject_type', '=', 'IdentityVerificationLog')
                    ->where('transaction_logs.action', '=', 'Identity review recorded');
            })
            ->select([
                'identity_verification_logs.id',
                'identity_verification_logs.beneficiary_id',
                'identity_verification_logs.applicant_name',
                'identity_verification_logs.decision',
                'identity_verification_logs.created_at',
                'transaction_logs.staff_name',
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

    public function destroy(Request $request, int $log): RedirectResponse
    {
        $validated = $request->validate([
            'staff_name' => ['required', 'string', 'max:255'],
        ]);

        $review = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->first(['id', 'captured_photo_path', 'id_image_path']);

        abort_if($review === null, 404);

        DB::transaction(function () use ($review, $validated): void {
            DB::table('identity_verification_logs')
                ->where('id', $review->id)
                ->delete();

            TransactionLog::record(
                $validated['staff_name'],
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
