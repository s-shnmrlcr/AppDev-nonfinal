<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | DSWD Payout Verification System</title>
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
            <p>Create an authorized staff account to access beneficiary verification, payout control, and reporting.</p>
            <div class="security-note">Authorized personnel only</div>
        </div>
    </div>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="mobile-brand">
                <div class="brand-mark">DSWD</div>
            </div>
            <div class="eyebrow">AUTHORIZED ACCESS</div>
            <h2>Create your account</h2>
            <p class="muted">Use your authorized DSWD email address to register.</p>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="form-stack">
                @csrf
                <label>Full name
                    <input type="text" name="name" value="{{ old('name') }}" autocomplete="name"
                        placeholder="Your full name" required>
                </label>

                <label>Email address
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                        placeholder="name@dswd.gov.ph" required>
                </label>

                <label>Password
                    <input type="password" name="password" autocomplete="new-password"
                        placeholder="At least 8 characters" minlength="8" required>
                </label>

                <label>Confirm password
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                        placeholder="Re-enter your password" minlength="8" required>
                </label>

                <button class="button button-primary button-full">Create account</button>
            </form>

            <p class="muted">Already have an account? <a href="{{ route('login') }}" class="text-link">Sign in</a></p>
        </div>
    </main>
</body>

</html>
