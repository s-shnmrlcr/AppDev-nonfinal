<?php

namespace App\Http\Controllers;

use App\Support\DemoBeneficiaries;

class VerificationController extends Controller
{
    public function index(?string $beneficiary = null)
    {
        $beneficiary = DemoBeneficiaries::find($beneficiary ?? 'BEN-00001');
        abort_if($beneficiary === null, 404);

        return view('verification.index', compact('beneficiary'));
    }
}
