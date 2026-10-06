<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | DSWD Payout Verification System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="auth-page">
    <div class="auth-visual">
        <div class="auth-overlay"></div>
        <div class="auth-copy">
            <div class="brand-mark large">DSWD</div>
            <div class="eyebrow">PAYOUT VERIFICATION PORTAL</div>
            <h1>Verify assistance.<br>Release with confidence.</h1>
            <p>A centralized prototype for beneficiary verification, payout control, documentation, and auditability.
            </p>
            <div class="security-note">Authorized personnel only</div>
        </div>
    </div>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="mobile-brand">
                <div class="brand-mark">DSWD</div>
            </div>
            <div class="eyebrow">AUTHORIZED ACCESS</div>
            <h2>Sign in to the portal</h2>
            <p class="muted">Use your authorized organization email to continue.</p>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="form-stack">
                @csrf
                <label>Email address
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@dswd.gov.ph"
                        required>
                </label>

                <label>Password
                    <div class="password-field">
                        <input type="password" name="password" id="password" placeholder="Enter your password"
                            required>
                        <button type="button" id="togglePassword">Show</button>
                    </div>
                </label>

                <div class="form-row between">
                    <label class="checkbox-label"><input type="checkbox" name="remember"> Remember me</label>
                    <a href="#" class="text-link">Forgot password?</a>
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
