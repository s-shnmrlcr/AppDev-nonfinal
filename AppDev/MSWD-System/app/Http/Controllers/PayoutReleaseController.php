<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;


class PayoutReleaseController extends Controller
{
    public function index(Payout $payout)
    {
        $payout->load([
            'beneficiary.barangay',
            'program',
            'releases',
        ]);

        return view('payout-releases.index', compact('payout'));
    }

}
