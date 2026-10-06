<header class="topbar">
    <div class="topbar-left">
        <button class="icon-button mobile-menu" id="mobileMenu" aria-label="Open navigation">☰</button>
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="breadcrumb">DSWD Portal / @yield('breadcrumb', 'Dashboard')</div>
        </div>
    </div>

    <div class="topbar-actions">
        <button class="icon-button notification-button" title="Notifications">
            <span>♢</span><i></i>
        </button>
        <div class="user-menu">
            <div class="avatar">AS</div>
            <div class="user-meta">
                <strong>{{ session('demo_user.name', 'Authorized Staff') }}</strong>
                <span>{{ session('demo_user.role', 'Staff') }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button" title="Sign out">Sign out</button>
        </form>
    </div>
</header>
