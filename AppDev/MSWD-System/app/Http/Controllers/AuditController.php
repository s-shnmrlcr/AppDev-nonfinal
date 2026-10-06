<?php

namespace App\Http\Controllers;

class AuditController extends Controller
{
    public function index()
    {
        $logs = [
            ['user'=>'Admin User','role'=>'Administrator','action'=>'Updated beneficiary','module'=>'Beneficiaries','target'=>'BEN-00005','status'=>'Success','time'=>'Oct 06, 2026 10:42 AM'],
            ['user'=>'Authorized Staff','role'=>'Verifier','action'=>'Confirmed identity','module'=>'Verification','target'=>'BEN-00001','status'=>'Success','time'=>'Oct 06, 2026 10:31 AM'],
            ['user'=>'Release Personnel','role'=>'Release Personnel','action'=>'Released payout','module'=>'Payouts','target'=>'PAY-2026-000184','status'=>'Success','time'=>'Oct 06, 2026 09:55 AM'],
            ['user'=>'Authorized Staff','role'=>'Staff','action'=>'Uploaded valid ID','module'=>'Verification','target'=>'BEN-00001','status'=>'Success','time'=>'Oct 06, 2026 09:12 AM'],
        ];

        return view('audit.index', compact('logs'));
    }
}
