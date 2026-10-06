<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    public function index()
    {
        $users = [
            ['name'=>'Admin User','email'=>'admin@dswd.gov.ph','role'=>'Administrator','status'=>'Active'],
            ['name'=>'Authorized Staff','email'=>'staff@dswd.gov.ph','role'=>'Authorized Personnel / Staff','status'=>'Active'],
            ['name'=>'Verification Officer','email'=>'verifier@dswd.gov.ph','role'=>'Verifier','status'=>'Active'],
            ['name'=>'Release Personnel','email'=>'release@dswd.gov.ph','role'=>'Release Personnel','status'=>'Active'],
        ];

        return view('users.index', compact('users'));
    }
}
