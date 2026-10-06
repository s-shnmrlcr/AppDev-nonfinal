@extends('layouts.app')
@section('title', 'Beneficiary Profile | DSWD Portal')
@section('page-title', 'Beneficiary Profile')
@section('breadcrumb', 'Beneficiaries / Profile')

@section('content')
    <div class="profile-head">
        <div class="profile-identity">
            <div class="profile-avatar">JD</div>
            <div>
                <div class="eyebrow">{{ $beneficiary['id'] }}</div>
                <h1>{{ $beneficiary['name'] }}</h1>
                <p>{{ $beneficiary['barangay'] }} · {{ $beneficiary['sector'] }}</p>
            </div>
        </div>
        <div class="button-group"><a href="{{ route('verification.index', $beneficiary['id']) }}"
                class="button button-primary">Open Verification</a><button class="button button-secondary">Edit</button></div>
    </div>

    <div class="profile-status-grid">
        <div><span>Eligibility</span><strong class="text-green">VERIFIED</strong></div>
        <div><span>Documents</span><strong class="text-green">COMPLETE</strong></div>
        <div><span>Identity Verification</span><strong class="text-green">CONFIRMED</strong></div>
        <div><span>Duplicate Assistance</span><strong>NONE</strong></div>
        <div><span>Payout Status</span><strong class="text-blue">READY FOR RELEASE</strong></div>
    </div>

    <div class="two-column">
        <section class="card">
            <div class="card-header">
                <div>
                    <h3>Personal Information</h3>
                    <p>Registered beneficiary details</p>
                </div>
            </div>
            <div class="detail-grid">
                <div><span>Full Name</span><strong>{{ $beneficiary['name'] }}</strong></div>
                <div><span>Beneficiary ID</span><strong>{{ $beneficiary['id'] }}</strong></div>
                <div><span>Date of Birth</span><strong>{{ $beneficiary['dob'] }}</strong></div>
                <div><span>Sex</span><strong>{{ $beneficiary['sex'] }}</strong></div>
                <div><span>Civil Status</span><strong>Married</strong></div>
                <div><span>Contact</span><strong>09XX XXX 1234</strong></div>
                <div class="full"><span>Address</span><strong>{{ $beneficiary['barangay'] }}, Prototype
                        Municipality</strong></div>
            </div>
        </section>
        <section class="card">
            <div class="card-header">
                <div>
                    <h3>Household Information</h3>
                    <p>Family and vulnerability indicators</p>
                </div>
            </div>
            <div class="detail-grid">
                <div><span>Household ID</span><strong>HH-2026-00001</strong></div>
                <div><span>Members</span><strong>4</strong></div>
                <div><span>Dependents</span><strong>3</strong></div>
                <div><span>Children</span><strong>2</strong></div>
                <div><span>Senior Citizens</span><strong>0</strong></div>
                <div><span>PWD</span><strong>0</strong></div>
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card-header">
            <div>
                <h3>Assistance History</h3>
                <p>Previous payout records are checked before release.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Emergency Assistance</td>
                        <td class="mono">PAY-2026-000102</td>
                        <td>₱3,000</td>
                        <td>Jun 18, 2026</td>
                        <td><x-status-badge status="Released" /></td>
                    </tr>
                    <tr>
                        <td>AYUDA 2025</td>
                        <td class="mono">PAY-2025-000821</td>
                        <td>₱4,000</td>
                        <td>Dec 12, 2025</td>
                        <td><x-status-badge status="Released" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
