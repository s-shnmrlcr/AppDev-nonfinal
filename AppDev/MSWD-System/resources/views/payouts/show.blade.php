@extends('layouts.app')
@php
    $isReadyForRelease = $payout['status'] === 'Ready for Release';
@endphp

@section('title', ($isReadyForRelease ? 'Confirm Payout' : 'Payout Details') . ' | DSWD Portal')
@section('page-title', $isReadyForRelease ? 'Confirm Payout Release' : 'Payout Details')
@section('breadcrumb', $isReadyForRelease ? 'Payout Releases / Confirm' : 'Payout Releases / Details')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $isReadyForRelease ? 'FINAL CONTROL' : 'PAYOUT DETAILS' }}</div>
            <h1>{{ $isReadyForRelease ? 'Confirm Payout Release' : 'Payout Details' }}</h1>
            <p>{{ $isReadyForRelease ? 'Review every condition before completing this important action.' : 'Review this payout and its current release status.' }}</p>
        </div>
    </div>
    <div class="confirmation-grid">
        <section class="card confirmation-card">
            <div class="confirmation-title"><span class="success-check">✓</span>
                <div>
                    <h3>{{ $payout['status'] }}</h3>
                    <p>{{ $isReadyForRelease ? 'All prototype release conditions are satisfied.' : 'This payout is not currently eligible for release.' }}</p>
                </div>
            </div>
            <div class="confirmation-list">
                <div><span>Beneficiary</span><strong>{{ $payout['beneficiary'] }}</strong></div>
                <div><span>Barangay</span><strong>{{ $payout['barangay'] }}</strong></div>
                <div><span>Program</span><strong>{{ $payout['program'] }}</strong></div>
                <div><span>Amount</span><strong>{{ $payout['amount'] }}</strong></div>
                @if ($isReadyForRelease)
                    <div><span>Verification</span><strong class="text-green">VERIFIED</strong></div>
                    <div><span>Previous Same Assistance</span><strong class="text-green">NONE</strong></div>
                    <div><span>Required Documents</span><strong class="text-green">COMPLETE</strong></div>
                    <div><span>Identity Verification</span><strong class="text-green">CONFIRMED</strong></div>
                @endif
            </div>
            @if ($isReadyForRelease)
                <div class="release-warning"><strong>Important:</strong> Confirming a payout is an important action. In
                    the production system this action should be transaction-protected and recorded in the audit log.</div>
            @else
                <div class="release-warning">This payout cannot be released while its status is
                    <strong>{{ $payout['status'] }}</strong>.</div>
            @endif
            <div class="form-actions"><a href="{{ route('payouts.index') }}"
                    class="button button-secondary">Back to Payouts</a>
                @if ($isReadyForRelease)
                    <button class="button button-primary" id="confirmRelease">Confirm Release</button>
                @endif
            </div>
        </section>
        @if ($isReadyForRelease)
            <section class="card">
                <div class="card-header">
                    <div>
                        <h3>Release Checklist</h3>
                        <p>Automated controls before payout</p>
                    </div>
                </div>
                <div class="checklist compact-checklist"><label><span>✓</span><strong>Verification
                            status</strong><b>PASS</b></label><label><span>✓</span><strong>Eligibility</strong><b>PASS</b></label><label><span>✓</span><strong>Documents
                            complete</strong><b>PASS</b></label><label><span>✓</span><strong>Duplicate assistance
                            check</strong><b>PASS</b></label><label><span>✓</span><strong>Barangay
                            consistency</strong><b>PASS</b></label></div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('confirmRelease')?.addEventListener('click', function() {
            if (confirm('Confirm payout release? This is an important action.')) {
                this.textContent = 'Payout Released';
                this.disabled = true;
                alert('Prototype result: PAYOUT SUCCESSFULLY RELEASED\nReference: {{ $payout['ref'] }}');
            }
        });
    </script>
@endpush
