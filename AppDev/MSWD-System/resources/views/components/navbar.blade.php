<header class="topbar">
    <div class="topbar-left">
        <button class="icon-button mobile-menu" id="mobileMenu" aria-label="Open navigation">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="breadcrumb">DSWD Portal / @yield('breadcrumb', 'Dashboard')</div>
        </div>
    </div>

    <div class="topbar-actions">
        <span class="avatar user-avatar" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5" /><path d="M5 20c.5-3.4 3.2-5.5 7-5.5s6.5 2.1 7 5.5" /></svg>
        </span>
        <div class="user-menu">
            <div class="user-meta">
                <strong>{{ auth()->user()->name }}</strong>
                <span>Authorized staff</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button" title="Sign out">Sign out</button>
        </form>
    </div>
</header>
