<?php

namespace App\Http\Controllers;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = [
            ['id'=>'PRG-2026-001','name'=>'AYUDA 2026','type'=>'Financial Assistance','amount'=>'₱5,000','coverage'=>'17 Barangays','status'=>'Active','end'=>'Dec 31, 2026'],
            ['id'=>'PRG-2026-002','name'=>'Emergency Assistance','type'=>'Emergency Aid','amount'=>'₱3,000','coverage'=>'17 Barangays','status'=>'Active','end'=>'Dec 31, 2026'],
            ['id'=>'PRG-2026-003','name'=>'Food Assistance','type'=>'In-kind Assistance','amount'=>'₱2,500','coverage'=>'Barangays 01–10','status'=>'Completed','end'=>'Sep 30, 2026'],
            ['id'=>'PRG-2026-004','name'=>'Senior Support','type'=>'Financial Assistance','amount'=>'₱4,000','coverage'=>'17 Barangays','status'=>'Draft','end'=>'TBD'],
        ];

        return view('programs.index', compact('programs'));
    }
}
