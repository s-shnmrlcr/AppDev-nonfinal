<?php

namespace App\Http\Controllers;

use App\Support\DemoBeneficiaries;

class BeneficiaryController extends Controller
{
    public function index()
    {
        $beneficiaries = DemoBeneficiaries::all();

        return view('beneficiaries.index', compact('beneficiaries'));
    }

    public function create()
    {
        $barangays = collect(range(1, 17))->map(fn ($i) => 'Barangay '.str_pad($i, 2, '0', STR_PAD_LEFT));

        return view('beneficiaries.create', compact('barangays'));
    }

    public function show(string $beneficiary)
    {
        $beneficiary = DemoBeneficiaries::find($beneficiary);
        abort_if($beneficiary === null, 404);

        return view('beneficiaries.show', compact('beneficiary'));
    }
}
