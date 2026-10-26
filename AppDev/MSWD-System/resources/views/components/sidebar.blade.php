<aside class="sidebar" id="sidebar">
    <div class="brand">
        <img class="brand-logo" src="{{ asset('images/branding/municipality-kabacan.png') }}" alt="">
        <div>
            <strong>MSWDO Kabacan</strong>
            <span>Payout Verification</span>
        </div>
    </div>

    <div class="sidebar-section-label">MAIN</div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></svg></span><span>Dashboard</span>
        </a>
        <a href="{{ route('beneficiaries.index') }}" class="{{ request()->routeIs('beneficiaries.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5" /><path d="M2.5 20c.4-3.7 2.7-5.5 6.5-5.5s6.1 1.8 6.5 5.5M16 5.5a3.5 3.5 0 0 1 0 6.8m1.5 2.2c2.3.7 3.6 2.5 4 5.5" /></svg></span><span>Beneficiaries</span>
        </a>
        <a href="{{ route('verification.index') }}" class="{{ request()->routeIs('verification.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z" /><path d="m9 12 2 2 4-4" /></svg></span><span>Verification Center</span>
        </a>
        <a href="{{ route('programs.index') }}" class="{{ request()->routeIs('programs.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6z" /><path d="M14 3v5h5M9 12h7M9 16h7" /></svg></span><span>Payout Programs</span>
        </a>
        <a href="{{ route('payouts.index') }}" class="{{ request()->routeIs('payouts.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18m-9 3h4" /></svg></span><span>Payout Releases</span>
        </a>
    </nav>

    <div class="sidebar-section-label">MANAGEMENT</div>
    <nav class="sidebar-nav">
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10m5 10V4m6 16v-7m5 7V7" /><path d="M2 20h20" /></svg></span><span>Reports</span>
        </a>
        <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg></span><span>Audit Logs</span>
        </a>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3" /><path d="M3 20c.4-3.5 2.5-5.2 6-5.2s5.6 1.7 6 5.2m1-11a3 3 0 0 1 0 5.8m1 2c2 .6 3.2 1.7 3.6 3.2" /></svg></span><span>User Management</span>
        </a>
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3" /><path d="m19.4 15 .1.1 1.2 2.1-2 2-2.1-1.2a8 8 0 0 1-1.6.7L14.5 21h-3l-.5-2.3a8 8 0 0 1-1.6-.7L7.3 19.2l-2-2 1.2-2.1a8 8 0 0 1-.7-1.6L3.5 13v-3l2.3-.5a8 8 0 0 1 .7-1.6L5.3 5.8l2-2 2.1 1.2a8 8 0 0 1 1.6-.7L11.5 2h3l.5 2.3a8 8 0 0 1 1.6.7l2.1-1.2 2 2-1.2 2.1a8 8 0 0 1 .7 1.6l2.3.5v3l-2.3.5a8 8 0 0 1-.7 1.5Z" /></svg></span><span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="workspace-label">SECURE WORKSPACE</div>
        <p>Authorized personnel access only.</p>
    </div>
</aside>
