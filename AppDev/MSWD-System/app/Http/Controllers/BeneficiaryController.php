<?php

namespace App\Http\Controllers;

use App\Support\DemoBeneficiaries;
use Illuminate\Support\Facades\DB;

class BeneficiaryController extends Controller
{
    public function index()
    {
        $beneficiaries = DemoBeneficiaries::all();
        // Gi add ko source ang values sa barangay table for search filter  --shijme
        $barangays = DB::table('barangays')->orderBy('name')->get(['id', 'name']);

        return view('beneficiaries.index', compact('beneficiaries', 'barangays'));
    }

    public function create()
    {
        // $barangays = collect(range(1, 17))->map(fn ($i) => 'Barangay '.str_pad($i, 2, '0', STR_PAD_LEFT));
        // Gi change ko ang source for barangays, sa database ko na gi connect ang values --shijme
        $barangays = DB::table('barangays')->orderBy('name')->get(['id', 'name']);

        return view('beneficiaries.create', compact('barangays'));
    }

    public function show(string $beneficiary)
    {
        $beneficiary = DemoBeneficiaries::find($beneficiary);
        abort_if($beneficiary === null, 404);

        return view('beneficiaries.show', compact('beneficiary'));
    }
}
