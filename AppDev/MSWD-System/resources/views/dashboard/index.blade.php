@extends('layouts.app')

@section('title', 'Dashboard | MSWDO Kabacan')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="page-heading dashboard-hero">
        <div class="dashboard-hero-content">
            <div class="dashboard-hero-meta">
                <span class="dashboard-hero-kicker">MSWDO <span aria-hidden="true">/</span> KABACAN</span>
                <span class="dashboard-live-tag"><span aria-hidden="true"></span> Current records</span>
            </div>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>A clear overview of beneficiaries, verification progress, and assistance releases.</p>
        </div>
        <a href="{{ route('beneficiaries.create') }}" class="button button-primary dashboard-hero-action">
            <span aria-hidden="true">＋</span> Register Beneficiary
        </a>
    </div>

    <div class="stat-grid dashboard-stats">
        @foreach ($stats as $stat)
            <div class="stat-card stat-card-{{ $stat['tone'] }}">
                <div class="stat-top">
                    <span class="stat-label">{{ $stat['label'] }}</span>
                    <span class="stat-icon {{ $stat['tone'] }}" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            @switch($stat['label'])
                                @case('Total Beneficiaries')
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                    @break
                                @case('Verified Beneficiaries')
                                    <path d="M20 11.1V12a8 8 0 1 1-4.75-7.33" />
                                    <path d="m8.5 11.5 2.5 2.5L21 4" />
                                    @break
                                @case('Pending Verification')
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                    @break
                                @default
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="M3 10h18M7 15h4" />
                            @endswitch
                        </svg>
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

    <div class="dashboard-section-heading">
        <div>
            <span>LIVE INSIGHTS</span>
            <h2>Program overview</h2>
        </div>
        <p>Verification, assistance, and recent activity at a glance.</p>
    </div>

    <div class="dashboard-grid">
        <section class="card chart-card chart-card-beneficiaries">
            <div class="card-header">
                <div>
                    <h3>Beneficiary Status</h3>
                    <p>Verification distribution</p>
                </div>
            </div>
            <div class="dashboard-status-chart" role="list" aria-label="Beneficiary verification status, {{ $beneficiaryChart['total'] }} records">
                @foreach ($beneficiaryChart['slices'] as $slice)
                    <div class="dashboard-status-column" role="listitem" aria-label="{{ $slice['label'] }}: {{ $slice['count'] }}">
                        <strong class="dashboard-status-count">{{ $slice['count'] }}</strong>
                        <span class="dashboard-status-track" aria-hidden="true">
                            <span
                                class="dashboard-status-bar"
                                style="--bar-height: {{ $slice['barHeight'] }}%; --bar-color: {{ $slice['color'] }}"
                            ></span>
                        </span>
                        <span class="dashboard-status-label">{{ $slice['label'] }}</span>
                    </div>
                @endforeach
            </div>
            @if ($beneficiaryChart['total'] === 0)
                <p class="dashboard-chart-empty-note">Verification data will appear as beneficiaries are added.</p>
            @endif
        </section>

        <section class="card chart-card chart-card-payouts">
            <div class="card-header">
                <div>
                    <h3>Payout Status</h3>
                    <p>Release progress</p>
                </div>
            </div>
            <div class="dashboard-status-chart" role="list" aria-label="Payout status, {{ $payoutChart['total'] }} records">
                @foreach ($payoutChart['slices'] as $slice)
                    <div class="dashboard-status-column" role="listitem" aria-label="{{ $slice['label'] }}: {{ $slice['count'] }}">
                        <strong class="dashboard-status-count">{{ $slice['count'] }}</strong>
                        <span class="dashboard-status-track" aria-hidden="true">
                            <span
                                class="dashboard-status-bar"
                                style="--bar-height: {{ $slice['barHeight'] }}%; --bar-color: {{ $slice['color'] }}"
                            ></span>
                        </span>
                        <span class="dashboard-status-label">{{ $slice['label'] }}</span>
                    </div>
                @endforeach
            </div>
            @if ($payoutChart['total'] === 0)
                <p class="dashboard-chart-empty-note">Payout progress will appear as beneficiaries are assigned.</p>
            @endif
        </section>

        <section class="card chart-card chart-card-barangay wide">
            <div class="card-header">
                <div>
                    <h3>Beneficiaries by Barangay</h3>
                    <p>Distribution across registered barangays</p>
                </div>
                <a href="{{ route('beneficiaries.index') }}" class="text-link">View masterlist</a>
            </div>
            @if ($barangayCounts->isNotEmpty())
                <div class="dashboard-barangay-grid" role="list" aria-label="Top barangays by beneficiary count">
                    @foreach ($barangayCounts as $barangay)
                        <div class="dashboard-barangay-card" role="listitem">
                            <div class="dashboard-barangay-card-top">
                                <span class="dashboard-barangay-name">{{ $barangay->name }}</span>
                                <span class="dashboard-barangay-count">
                                    <strong>{{ $barangay->total }}</strong>
                                    <span>beneficiaries</span>
                                </span>
                            </div>
                            <span class="dashboard-barangay-track" aria-hidden="true">
                                <span class="dashboard-barangay-fill" style="width: {{ $barangay->percentage }}%"></span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state compact-empty-state">
                    <p>Barangay totals will appear when beneficiary records are available.</p>
                </div>
            @endif
        </section>

        <section class="card activity-card wide">
            <div class="card-header">
                <div>
                    <h3>Recent Activities</h3>
                    <p>Latest system actions</p>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="text-link">View all</a>
            </div>
            @if ($recentActivities->isNotEmpty())
                <div class="activity-list">
                    @foreach ($recentActivities as $activity)
                        <div class="activity-item">
                            <span class="activity-marker" aria-hidden="true"></span>
                            <div class="activity-body">
                                <p>{{ $activity->action }}</p>
                                <span>
                                    @if ($activity->subject_type)
                                        {{ class_basename($activity->subject_type) }}{{ $activity->subject_id ? ' #' . $activity->subject_id : '' }}
                                        &middot;
                                    @endif
                                    <time datetime="{{ $activity->created_at->toIso8601String() }}" data-local-datetime>
                                        {{ $activity->created_at->format('M j, Y g:i A') }}
                                    </time>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state compact-empty-state">
                    <p>No recent activity has been recorded.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
