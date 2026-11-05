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
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    <section class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Reviewed By</th>
                        <th>Account</th>
                        <th>Applicant</th>
                        <th>Decision</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <time datetime="{{ \Illuminate\Support\Carbon::parse($log->created_at)->utc()->toIso8601String() }}"
                                      data-local-datetime>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('M j, Y g:i A') }}</time>
                            </td>
                            <td><strong>{{ $log->name }}</strong></td>
                            <td>{{ $log->email }}</td>
                            <td>
                                @if ($log->applicant_name)
                                    <strong>{{ $log->applicant_name }}</strong>
                                @else
                                    —
                                @endif
                            </td>
                            <td><x-status-badge :status="$log->decision === 'matched' ? 'Identity matches' : 'Identity does not match'" /></td>
                            <td>
                                <div class="audit-actions">
                                    <a href="{{ route('verification.logs.show', $log->id) }}" class="button button-secondary">Review</a>
                                    <form method="POST"
                                          action="{{ route('audit-logs.destroy', $log->id) }}"
                                          onsubmit="return confirm('Delete only this identity audit log and its saved images? The beneficiary record will not be changed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button-danger">Delete</button>
                                    </form>
                                </div>
                                @if (!$log->current_beneficiary_id)
                                    <span class="muted">Applicant no longer available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
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
