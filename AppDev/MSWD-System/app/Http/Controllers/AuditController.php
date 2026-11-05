<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditController extends Controller
{
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
                'users.name',
                'users.email',
                'beneficiaries.id as current_beneficiary_id',
            ])
            ->latest('identity_verification_logs.created_at')
            ->get();

        return view('audit.index', compact('logs'));
    }

    public function destroy(int $log): RedirectResponse
    {
        $review = DB::table('identity_verification_logs')
            ->where('id', $log)
            ->first(['id', 'captured_photo_path', 'id_image_path']);

        abort_if($review === null, 404);

        DB::table('identity_verification_logs')
            ->where('id', $review->id)
            ->delete();

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
