<?php

namespace App\Http\Controllers;

class ReportController extends Controller
{
    public function index()
    {
        $barangays = collect(range(1,17))->map(fn($i) => [
            'name' => 'Barangay '.str_pad($i,2,'0',STR_PAD_LEFT),
            'beneficiaries' => 50 + ($i * 7),
            'released' => 38 + ($i * 5),
        ]);

        return view('reports.index', compact('barangays'));
    }
}
