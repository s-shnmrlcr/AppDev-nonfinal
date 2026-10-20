<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegistration()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->hasAuthorizedEmailDomain($validated['email'])) {
            throw ValidationException::withMessages([
                'email' => 'Create an account using your authorized DSWD email address.',
            ]);
        }

        // Human nga mag-sign in ang bag-ong user; ayaw dayon siya i-authenticate human sa registration. -shaine
        User::create($validated);

        return redirect()
            ->route('login')
            ->with('status', 'Your account has been created. Sign in to continue.');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $this->hasAuthorizedEmailDomain($credentials['email'])) {
            throw ValidationException::withMessages([
                'email' => 'Only authorized DSWD personnel can access this portal.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an account.',
            ]);
        }
        // Ilisi ang session ID human sa login aron malikayan ang session fixation. -shaine
        $request->session()->regenerate();
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function hasAuthorizedEmailDomain(string $email): bool
    {
        $domain = strtolower(config('mswd.authorized_email_domain', '@dswd.gov.ph'));

        // Suffix ang i-check aron parehas ang resulta bisag uppercase ang domain sa input. -shaine
        return str_ends_with(strtolower($email), $domain);
    }

}
