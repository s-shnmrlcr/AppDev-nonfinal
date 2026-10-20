<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        $logs = DB::table('identity_verification_logs')
            ->join('users', 'identity_verification_logs.user_id', '=', 'users.id')
            ->select([
                'identity_verification_logs.decision',
                'identity_verification_logs.created_at',
                'users.name',
                'users.email',
            ])
            ->latest('identity_verification_logs.created_at')
            ->get();

        return view('audit.index', compact('logs'));
    }
}
