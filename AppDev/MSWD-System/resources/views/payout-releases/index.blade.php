
@extends('layouts.app')

@section('title', 'Payout Releases | MSWDO Kabacan')
@section('page-title', 'Payout Releases')
@section('breadcrumb', 'Payouts / Release Details')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="page-heading">
        <div>
            <div class="eyebrow">RELEASE CONTROL</div>
            <h1>Payout Release Details</h1>
            <p>View beneficiary assistance and its release history.</p>
        </div>

        <a href="{{ route('payouts.index') }}" class="button button-secondary">
            Back to Payouts
        </a>
    </div>

    {{-- Display success or error messages --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Payout information --}}
    <div class="card">
        <h3>Payout Information</h3>

        <div class="program-meta">
            <span>
                Reference
                <strong>{{ $payout->reference }}</strong>
            </span>

            <span>
                Beneficiary
                <strong>
                    {{ $payout->beneficiary->first_name }}
                    {{ $payout->beneficiary->last_name }}
                </strong>
            </span>

            <span>
                Program
                <strong>{{ $payout->program->program_name }}</strong>
            </span>

            <span>
                Assistance Amount
                <strong>₱{{ number_format((float) $payout->amount, 2) }}</strong>
            </span>

            <span>
                Payout Status
                <strong>{{ $payout->status }}</strong>
            </span>
        </div>
    </div>


    {{-- Record a new assistance release --}}
    <div class="card">
        <h3>Record Release</h3>

        <p>
            Remaining Balance:
            <strong>
                ₱{{ number_format($remainingBalance, 2) }}
            </strong>
        </p>

        @if ($remainingBalance > 0)

            <form method="POST"
                action="{{ route('payout-releases.store', $payout->id) }}">

                @csrf

                {{-- Staff only enters the release amount --}}
                <div class="form-group">
                    <label for="amount">Release Amount (₱)</label>

                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        class="form-control"
                        min="0.01"
                        max="{{ number_format($remainingBalance, 2, '.', '') }}"
                        step="0.01"
                        value="{{ old('amount') }}"
                        placeholder="Enter amount to release"
                        required
                    >
                </div>

                <button type="submit" class="button button-primary">
                    Record Release
                </button>

            </form>

        @else

            <div class="alert alert-success">
                This payout has been fully released.
            </div>

        @endif
    </div>


    {{-- Release history --}}
    <div class="card">
        <h3>Release History</h3>
        <p>Records of assistance releases for this beneficiary.</p>

        @if ($payout->releases->isEmpty())

            <div class="empty-state">
                <strong>No release records yet</strong>
                <p>No assistance has been recorded as released.</p>
            </div>

        @else

            <div class="table-responsive table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Release Reference</th>
                            <th>Amount</th>
                            <th>Release Date</th>
                            <th>Released By</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($payout->releases as $release)
                            <tr>
                                <td>{{ $release->reference }}</td>

                                <td>
                                    ₱{{ number_format((float) $release->amount, 2) }}
                                </td>

                                <td>
                                    {{ $release->released_at
                                        ? $release->released_at->format('M d, Y h:i A')
                                        : '—' }}
                                </td>

                                <td>{{ $release->released_by ?? '—' }}</td>

                                <td>
                                    <x-status-badge :status="$release->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif
    </div>

@endsection
