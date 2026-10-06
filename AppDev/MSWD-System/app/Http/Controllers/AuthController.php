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

        $user = User::create($validated);
        Auth::login($user);
        $request->session()->regenerate();
        $this->storeDemoUser($user);

        return redirect()->route('dashboard');
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
                'email' => 'Only authorized DSWD personnel can access this prototype.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an account.',
            ]);
        }

        $request->session()->regenerate();
        $this->storeDemoUser(Auth::user());

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

        return str_ends_with(strtolower($email), $domain);
    }

    private function storeDemoUser(User $user): void
    {
        session([
            'demo_user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'Authorized Personnel / Staff',
            ],
        ]);
    }
}
