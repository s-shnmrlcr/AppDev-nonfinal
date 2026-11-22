<?php
// -shaine: Handles staff sign-in, session renewal, and sign-out.

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // -shaine: Displays the staff sign-in form.
    public function showLogin()
    {
        return view('auth.login');
    }

    // -shaine: Authenticates the approved admin account and rotates its session ID.
    public function login(Request $request): RedirectResponse
    {
        // -shaine: Normalize casing and surrounding spaces before checking the authorized account.
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check if the email matches the authorized Admin email. -Dan
        $adminEmail = strtolower(trim((string) config('mswd.admin_email')));

        if ($credentials['email'] !== $adminEmail) {
            throw ValidationException::withMessages([
                'email' => 'Only the authorized Admin account can access this portal.',
            ]);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an account.',
            ]);
        }

        // Check if the authenticated user has Admin role -Dan
        if (strcasecmp((string) Auth::user()->role, 'Admin') !== 0) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'You are not authorized to access this portal.',
            ]);
        }

        // Ilisi ang session ID human sa login aron malikayan ang session fixation. -shaine
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    // -shaine: Clears the authentication session and redirects staff to sign-in.
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

}
