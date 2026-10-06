@extends('layouts.app')
@section('title', 'Payout Releases | DSWD Portal')
@section('page-title', 'Payout Releases')
@section('breadcrumb', 'Payout Releases')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">RELEASE CONTROL</div>
            <h1>Payout Releases</h1>
            <p>Review eligible beneficiaries and control final payout release.</p>
        </div>
    </div>
    <div class="release-alert"><strong>Release rule:</strong> Only verified, eligible beneficiaries with complete documents
        and no duplicate assistance may proceed.</div>
    <section class="card">
        <div class="filter-toolbar">
            <div class="search-box"><span>⌕</span><input type="search"
                    placeholder="Search payout reference or beneficiary..."></div><select>
                <option>All statuses</option>
                <option>Released</option>
                <option>Ready for Release</option>
                <option>Blocked</option>
            </select><select>
                <option>AYUDA 2026</option>
            </select>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Beneficiary</th>
                        <th>Barangay</th>
                        <th>Program</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payouts as $p)
                        <tr>
                            <td class="mono">{{ $p['ref'] }}</td>
                            <td><strong>{{ $p['beneficiary'] }}</strong></td>
                            <td>{{ $p['barangay'] }}</td>
                            <td>{{ $p['program'] }}</td>
                            <td>{{ $p['amount'] }}</td>
                            <td>{{ $p['date'] }}</td>
                            <td><x-status-badge :status="$p['status']" /></td>
                            <td><a class="table-action" href="{{ route('payouts.show', $p['ref']) }}">Review</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
