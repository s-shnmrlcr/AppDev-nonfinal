@extends('layouts.app')

@section('title', 'Dashboard | MSWDO Kabacan')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">OVERVIEW</div>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>Monitor beneficiaries, verification progress, and payout activity from one workspace.</p>
        </div>
        <a href="{{ route('beneficiaries.create') }}" class="button button-primary">
            <span aria-hidden="true">＋</span> Add Beneficiary
        </a>
    </div>

    <div class="stat-grid">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">{{ $stat['label'] }}</span>
                    <span class="stat-icon {{ $stat['tone'] }}" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M8 15l4-4 3 3 5-7" /></svg>
                    </span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-caption">
                    {{-- Ibutang ang klaro nga kahulugan sa matag total aron dili parehas ang placeholder. -shaine --}}
                    @switch($stat['label'])
                        @case('Total Beneficiaries')
                            Current registered total
                            @break
                        @case('Verified Beneficiaries')
                            Records cleared for assistance
                            @break
                        @case('Pending Verification')
                            Awaiting staff review
                            @break
                        @case('Payouts Released')
                            Completed releases
                            @break
                        @default
                            Current recorded total
                    @endswitch
                </div>
            </div>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <section class="card chart-card">
            <div class="card-header">
                <div>
                    <h3>Beneficiary Status</h3>
                    <p>Verification distribution</p>
                </div>
            </div>
            <div class="empty-state compact-empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 3v9h9M19.1 15A8 8 0 1 1 9 4.1" /></svg>
                </span>
                <p>Verification data will appear when records are available.</p>
            </div>
        </section>

        <section class="card chart-card">
            <div class="card-header">
                <div>
                    <h3>Payout Status</h3>
                    <p>Release progress</p>
                </div>
            </div>
            <div class="empty-state compact-empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18m-9 3h4" /></svg>
                </span>
                <p>Payout data will appear when records are available.</p>
            </div>
        </section>

        <section class="card chart-card wide">
            <div class="card-header">
                <div>
                    <h3>Beneficiaries by Barangay</h3>
                    <p>Distribution across registered barangays</p>
                </div>
                <a href="{{ route('beneficiaries.index') }}" class="text-link">View masterlist</a>
            </div>
            <div class="empty-state compact-empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 20V10m5 10V4m6 16v-7m5 7V7M2 20h20" /></svg>
                </span>
                <p>Barangay totals will appear when beneficiary records are available.</p>
            </div>
        </section>

        <section class="card activity-card">
            <div class="card-header">
                <div>
                    <h3>Recent Activities</h3>
                    <p>Latest system actions</p>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="text-link">View all</a>
            </div>
            <div class="empty-state compact-empty-state">
                <p>No recent activity.</p>
            </div>
        </section>
    </div>
@endsection
