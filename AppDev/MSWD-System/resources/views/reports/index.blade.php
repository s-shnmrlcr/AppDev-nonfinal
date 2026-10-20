@extends('layouts.app')
@section('title', 'Reports | DSWD Portal')
@section('page-title', 'Reports')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">REPORTING</div>
            <h1>Reports & Masterlists</h1>
            <p>Review beneficiary, verification, payout, barangay, and assistance statistics.</p>
        </div>
    </div>
    <div class="report-cards">
        <div class="card report-card"><span>Beneficiary Report</span><strong>{{ number_format($beneficiaryCount) }}</strong><small>Registered beneficiaries</small></div>
        <div class="card report-card"><span>Verification Report</span><strong>{{ number_format($verifiedCount) }}</strong><small>Verified records</small></div>
        <div class="card report-card"><span>Payout Report</span><strong>₱{{ number_format($releasedAmount, 2) }}</strong><small>{{ number_format($releasedCount) }} releases</small></div>
        <div class="card report-card"><span>Barangay Report</span><strong>{{ $barangays->count() }}</strong><small>Registered barangays</small></div>
    </div>
    <section class="card">
        <div class="card-header">
            <div>
                <h3>Barangay Release Overview</h3>
                <p>Current beneficiary and payout totals by barangay.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th>Beneficiaries</th>
                        <th>Released</th>
                        <th>Pending</th>
                        <th>Release Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangays as $b)
                        <tr>
                            <td><strong>{{ $b['name'] }}</strong></td>
                            <td>{{ $b['beneficiaries'] }}</td>
                            <td>{{ $b['released'] }}</td>
                            <td>{{ $b['beneficiaries'] - $b['released'] }}</td>
                            <td>{{ $b['beneficiaries'] > 0 ? round(($b['released'] / $b['beneficiaries']) * 100) : 0 }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state table-empty-state">
                                    <strong>No barangays available</strong>
                                    <p>Barangay report data will appear when locations are configured.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
