@extends('layouts.app')
@section('title', 'Audit Logs | MSWDO Kabacan')
@section('page-title', 'Audit Logs')
@section('breadcrumb', 'Audit Logs')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">TRACEABILITY</div>
            <h1>Audit Logs</h1>
            <p>Every important system action is recorded for review.</p>
        </div>
    </div>
    <section class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Reviewed By</th>
                        <th>Account</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('M j, Y g:i A') }}</td>
                            <td><strong>{{ $log->name }}</strong></td>
                            <td>{{ $log->email }}</td>
                            <td><x-status-badge :status="$log->decision === 'matched' ? 'Identity matches' : 'Identity does not match'" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state table-empty-state">
                                    <strong>No identity reviews recorded</strong>
                                    <p>Recorded claimant identity decisions will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
