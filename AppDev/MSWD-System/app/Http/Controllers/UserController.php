<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('name')->get();

        return view('users.index', compact('users'));
    }
}
