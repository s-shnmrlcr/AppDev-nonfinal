@extends('layouts.app')

@section('title', 'Dashboard | DSWD Portal')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">OVERVIEW</div>
            <h1>Good morning, {{ session('demo_user.name', 'Authorized Staff') }}</h1>
            <p>Monitor beneficiaries, verification progress, and payout activity from one workspace.</p>
        </div>
        <a href="{{ route('beneficiaries.create') }}" class="button button-primary">+ Add Beneficiary</a>
    </div>

    <div class="stat-grid">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">{{ $stat['label'] }}</span>
                    <span class="stat-icon {{ $stat['tone'] }}"></span>
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-change {{ $stat['tone'] }}">{{ $stat['change'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <section class="card chart-card">
            <div class="card-header">
                <div>
                    <h3>Beneficiary Status</h3>
                    <p>Current verification distribution</p>
                </div>
                <button class="icon-button">•••</button>
            </div>
            <div class="chart-wrap donut-wrap"><canvas id="beneficiaryChart"></canvas></div>
            <div class="legend-row">
                <span><i class="dot green"></i> Verified <strong>980</strong></span>
                <span><i class="dot amber"></i> Pending <strong>180</strong></span>
                <span><i class="dot red"></i> Rejected <strong>90</strong></span>
            </div>
        </section>

        <section class="card chart-card">
            <div class="card-header">
                <div>
                    <h3>Payout Status</h3>
                    <p>Release progress for active programs</p>
                </div>
                <button class="icon-button">•••</button>
            </div>
            <div class="chart-wrap"><canvas id="payoutChart"></canvas></div>
        </section>

        <section class="card chart-card wide">
            <div class="card-header">
                <div>
                    <h3>Beneficiaries by Barangay</h3>
                    <p>Registered beneficiaries across the 17 barangays</p>
                </div>
                <a href="{{ route('beneficiaries.index') }}" class="text-link">View masterlist</a>
            </div>
            <div class="chart-wrap bar-height"><canvas id="barangayChart"></canvas></div>
        </section>

        <section class="card activity-card">
            <div class="card-header">
                <div>
                    <h3>Recent Activities</h3>
                    <p>Latest system actions</p>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="text-link">View all</a>
            </div>
            <div class="activity-list">
                @foreach ($activities as $activity)
                    <div class="activity-item">
                        <div class="activity-marker {{ strtolower($activity['type']) }}"></div>
                        <div class="activity-body">
                            <p><strong>{{ $activity['name'] }}</strong> {{ $activity['action'] }}</p>
                            <span>{{ $activity['time'] }} · {{ $activity['type'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        new Chart(document.getElementById('beneficiaryChart'), {
            type: 'doughnut',
            data: {
                labels: ['Verified', 'Pending', 'Rejected'],
                datasets: [{
                    data: [980, 180, 90],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        new Chart(document.getElementById('payoutChart'), {
            type: 'bar',
            data: {
                labels: ['Released', 'Pending', 'Cancelled'],
                datasets: [{
                    label: 'Payouts',
                    data: [850, 130, 18],
                    borderRadius: 8,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eef1f5'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        new Chart(document.getElementById('barangayChart'), {
            type: 'bar',
            data: {
                labels: Array.from({
                    length: 17
                }, (_, i) => 'B' + String(i + 1).padStart(2, '0')),
                datasets: [{
                    label: 'Beneficiaries',
                    data: [72, 84, 96, 62, 88, 75, 104, 69, 91, 57, 81, 77, 95, 64, 86, 73, 98],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eef1f5'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endpush
