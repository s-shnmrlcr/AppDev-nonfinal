@extends('layouts.app')
@section('title', 'Payout Releases | MSWDO Kabacan')
@section('page-title', 'Payout Releases')
@section('breadcrumb', 'Payout Releases')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">RELEASE CONTROL</div>
            <h1>Payout Releases</h1>
            <p>Review payout release records.</p>
        </div>
        <a href="{{ route('payouts.export') }}" class="button button-primary">Export Releases CSV</a>
    </div>
    <section class="card">
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payouts as $p)
                        <tr>
                            <td class="mono">{{ $p['ref'] }}</td>
                            <td><strong>{{ $p['beneficiary'] }}</strong></td>
                            <td>{{ $p['barangay'] }}</td>
                            <td>{{ $p['program'] }}</td>
                            <td>{{ $p['amount'] }}</td>
                            <td>{{ $p['date'] }}</td>
                            <td><x-status-badge :status="$p['status']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state table-empty-state">
                                    <strong>No payout records</strong>
                                    <p>Payout records will appear here when they are available.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
