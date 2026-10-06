@extends('layouts.app')
@section('title', 'Payout Programs | DSWD Portal')
@section('page-title', 'Payout Programs')
@section('breadcrumb', 'Payout Programs')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>Payout Programs</h1>
            <p>Manage assistance programs, eligibility criteria, and coverage.</p>
        </div><button class="button button-primary">+ Create Program</button>
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
                        date<strong>{{ $program['end'] }}</strong></span></div><button
                    class="button button-secondary button-full">View Program</button>
            </div>
        @endforeach
    </div>
@endsection
