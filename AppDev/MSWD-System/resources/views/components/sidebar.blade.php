<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark">DSWD</div>
        <div>
            <strong>DSWD Portal</strong>
            <span>Payout Verification</span>
        </div>
    </div>

    <div class="sidebar-section-label">MAIN</div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">▦</span><span>Dashboard</span>
        </a>
        <a href="{{ route('beneficiaries.index') }}" class="{{ request()->routeIs('beneficiaries.*') ? 'active' : '' }}">
            <span class="nav-icon">◉</span><span>Beneficiaries</span>
        </a>
        <a href="{{ route('verification.index') }}" class="{{ request()->routeIs('verification.*') ? 'active' : '' }}">
            <span class="nav-icon">✓</span><span>Verification Center</span>
        </a>
        <a href="{{ route('programs.index') }}" class="{{ request()->routeIs('programs.*') ? 'active' : '' }}">
            <span class="nav-icon">▤</span><span>Payout Programs</span>
        </a>
        <a href="{{ route('payouts.index') }}" class="{{ request()->routeIs('payouts.*') ? 'active' : '' }}">
            <span class="nav-icon">₱</span><span>Payout Releases</span>
        </a>
    </nav>

    <div class="sidebar-section-label">MANAGEMENT</div>
    <nav class="sidebar-nav">
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <span class="nav-icon">▥</span><span>Reports</span>
        </a>
        <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
            <span class="nav-icon">◷</span><span>Audit Logs</span>
        </a>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <span class="nav-icon">◎</span><span>User Management</span>
        </a>
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <span class="nav-icon">⚙</span><span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="prototype-label">PROTOTYPE</div>
        <p>For authorized personnel and demonstration purposes.</p>
    </div>
</aside>
