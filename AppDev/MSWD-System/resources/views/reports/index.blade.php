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
        <div class="button-group"><button class="button button-secondary">Export CSV</button><button
                class="button button-primary">Print Report</button></div>
    </div>
    <div class="report-cards">
        <div class="card report-card"><span>Beneficiary Report</span><strong>1,250</strong><small>Registered
                beneficiaries</small></div>
        <div class="card report-card"><span>Verification Report</span><strong>980</strong><small>Verified records</small>
        </div>
        <div class="card report-card"><span>Payout Report</span><strong>₱4.25M</strong><small>Released amount</small></div>
        <div class="card report-card"><span>Barangay Report</span><strong>17</strong><small>Active prototype
                barangays</small></div>
    </div>
    <section class="card">
        <div class="card-header">
            <div>
                <h3>Barangay Release Overview</h3>
                <p>Prototype summary for all 17 barangays.</p>
            </div><select>
                <option>This month</option>
                <option>This week</option>
                <option>Today</option>
            </select>
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
                    @foreach ($barangays as $b)
                        <tr>
                            <td><strong>{{ $b['name'] }}</strong></td>
                            <td>{{ $b['beneficiaries'] }}</td>
                            <td>{{ $b['released'] }}</td>
                            <td>{{ $b['beneficiaries'] - $b['released'] }}</td>
                            <td>{{ round(($b['released'] / $b['beneficiaries']) * 100) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
