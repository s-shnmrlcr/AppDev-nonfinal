<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | MSWDO Kabacan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/municipality-kabacan.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="auth-page">
    <div class="auth-visual">
        <div class="auth-overlay"></div>
        <div class="auth-copy">
            <div class="auth-branding">
                <div class="auth-site-lockup">
                    <img src="{{ asset('images/branding/municipality-kabacan.png') }}" alt="">
                    <span><strong>MSWDO Kabacan</strong><small>Municipal Social Welfare and Development Office</small></span>
                </div>
                <div class="auth-logo-grid" aria-label="Government and local logos">
                    <div class="auth-logo-tile auth-logo-wide"><img src="{{ asset('images/branding/unlad-kabacan.jpg') }}" alt="Unlad Kabacan"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/dswd-logo.png') }}" alt="DSWD"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/municipality-kabacan.png') }}" alt="Municipality of Kabacan"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/cotabato-province-seal.png') }}" alt="Province of Cotabato"></div>
                    <div class="auth-logo-tile auth-logo-wide"><img src="{{ asset('images/branding/dswd-bagong-pilipinas.png') }}" alt="DSWD and Bagong Pilipinas"></div>
                </div>
            </div>
            <div class="auth-message">
                <div class="eyebrow">PAYOUT VERIFICATION PORTAL</div>
                <h1>Verify assistance.<br>Release with confidence.</h1>
                <p>A secure workspace for beneficiary verification, payout control, documentation, and auditability.</p>
            </div>
            <div class="security-note">Authorized personnel only</div>
        </div>
    </div>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="mobile-brand">
                <div class="auth-site-lockup">
                    <img src="{{ asset('images/branding/municipality-kabacan.png') }}" alt="">
                    <span><strong>MSWDO Kabacan</strong><small>Municipal Social Welfare and Development Office</small></span>
                </div>
                <div class="auth-logo-grid" aria-label="Government and local logos">
                    <div class="auth-logo-tile auth-logo-wide"><img src="{{ asset('images/branding/unlad-kabacan.jpg') }}" alt="Unlad Kabacan"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/dswd-logo.png') }}" alt="DSWD"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/municipality-kabacan.png') }}" alt="Municipality of Kabacan"></div>
                    <div class="auth-logo-tile"><img src="{{ asset('images/branding/cotabato-province-seal.png') }}" alt="Province of Cotabato"></div>
                    <div class="auth-logo-tile auth-logo-wide"><img src="{{ asset('images/branding/dswd-bagong-pilipinas.png') }}" alt="DSWD and Bagong Pilipinas"></div>
                </div>
            </div>
            <div class="eyebrow">AUTHORIZED ACCESS</div>
            <h2>Sign in to the portal</h2>
            <p class="muted">Use your authorized organization email to continue.</p>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="form-stack">
                @csrf
                <label>Email address
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </label>

                <label>Password
                    <div class="password-field">
                        <input type="password" name="password" id="password" autocomplete="current-password" required>
                        <button type="button" id="togglePassword">Show</button>
                    </div>
                </label>

                <div class="between">
                    <label class="checkbox-label"><input type="checkbox" name="remember"> Remember me</label>
                </div>

                <button class="button button-primary button-full">Sign in</button>
            </form>

            <p class="muted">Need an account? <a href="{{ route('register') }}" class="text-link">Create account</a></p>
        </div>
    </main>

    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const input = document.getElementById('password');
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            this.textContent = hidden ? 'Hide' : 'Show';
        });
    </script>
</body>

</html>
