<?php
// -shaine: Loads staff account information for user management.

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    // -shaine: Lists staff accounts in display-name order.
    public function index()
    {
        $users = User::query()->orderBy('name')->get();

        return view('users.index', compact('users'));
    }
}
