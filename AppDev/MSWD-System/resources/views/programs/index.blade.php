@extends('layouts.app')
@section('title', 'Payout Programs | MSWDO Kabacan')
@section('page-title', 'Payout Programs')
@section('breadcrumb', 'Payout Programs')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>Payout Programs</h1>
            <p>Manage assistance programs, eligibility criteria, and coverage.</p>
        </div><a href="{{ route('programs.create') }}" class="button button-primary">+ Create Program</a>
    </div>
    <div class="card-grid">
        @foreach ($programs as $program)
            <div class="card program-card">
                <div class="program-top"><span class="program-id">{{ $program['id'] }}</span><x-status-badge
                        :status="$program['status']" /></div>
                <h3>{{ $program['name'] }}</h3>
                <p>{{ $program['type'] }}</p>
                <div class="program-amount">{{ $program['amount'] }}</div>
                <div class="program-meta"><span>Coverage<strong>{{ $program['coverage'] }}</strong></span><span>End
                    date<strong>{{ $program['end'] }}</strong></span></div>
            </div>
        @endforeach
        @if (count($programs) === 0)
            <div class="card empty-state page-empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6z" /><path d="M14 3v5h5M9 12h7M9 16h7" /></svg>
                </span>
                <strong>No payout programs</strong>
                <p>Program information will appear here when it is available.</p>
            </div>
        @endif
    </div>
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
@endsection
