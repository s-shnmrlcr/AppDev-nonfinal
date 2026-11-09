@extends('layouts.app')

@section('title', 'Manage Beneficiaries | MSWDO Kabacan')
@section('page-title', 'Manage Beneficiaries')
@section('breadcrumb', 'Payout Programs / Beneficiaries')

@section('content')

    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>{{ $program->program_name }}</h1>
            <p>Assign verified beneficiaries to this payout program.</p>
        </div>

        <a href="{{ route('programs.index') }}" class="button button-secondary">
            Back to Programs
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <h3>Program Information</h3>

        <p><strong>Program:</strong> {{ $program->program_name }}</p>
        <p><strong>Total Budget:</strong> ₱{{ number_format((float) $program->budget, 2) }}</p>
        <p><strong>Allocated:</strong> ₱{{ number_format((float) $program->payouts()->sum('amount'), 2) }}</p>
        <p>
            <strong>Remaining Budget:</strong>
            ₱{{ number_format((float) $program->budget - (float) $program->payouts()->sum('amount'), 2) }}
        </p>
    </div>

    <div class="card">
        <h3>Assign Verified Beneficiaries</h3>

        @if ($availableBeneficiaries->isEmpty())

            <p>No available verified beneficiaries to assign.</p>

        @else

            <form method="POST"
                  action="{{ route('programs.beneficiaries.assign', $program->id) }}">

                @csrf

                <div class="form-group">
                    <label for="amount">Assistance Amount per Beneficiary</label>

                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        class="form-control"
                        min="0.01"
                        step="0.01"
                        value="{{ old('amount') }}"
                        placeholder="Enter assistance amount"
                        required
                    >

                    <small>One amount will be applied to every selected beneficiary.</small>
                </div>

                <div class="form-group">
                    <label>Select Beneficiaries</label>

                    @foreach ($availableBeneficiaries as $beneficiary)

                        <div class="beneficiary-option">
                            <label>
                                <input
                                    type="checkbox"
                                    name="beneficiary_ids[]"
                                    value="{{ $beneficiary->id }}"
                                    {{ in_array($beneficiary->id, old('beneficiary_ids', [])) ? 'checked' : '' }}
                                >

                                {{ $beneficiary->first_name }}
                                {{ $beneficiary->middle_name }}
                                {{ $beneficiary->last_name }}
                                {{ $beneficiary->suffix }}

                                <span class="badge">Verified</span>
                            </label>
                        </div>

                    @endforeach
                </div>

                <button type="submit" class="button button-primary">
                    Assign Selected Beneficiaries
                </button>

            </form>

        @endif
    </div>

    <div class="card">
        <h3>Assigned Beneficiaries</h3>

        @if ($assignedPayouts->isEmpty())

            <p>No beneficiaries have been assigned to this program yet.</p>

        @else

            <div class="table-responsive table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Beneficiary</th>
                            <th>Assistance Amount</th>
                            <th>Payout Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($assignedPayouts as $payout)
                            <tr>
                                <td>{{ $payout->reference }}</td>

                                <td>
                                    {{ $payout->beneficiary->first_name }}
                                    {{ $payout->beneficiary->last_name }}
                                </td>

                                <td>
                                    ₱{{ number_format((float) $payout->amount, 2) }}
                                </td>

                                <td>
                                    <x-status-badge :status="$payout->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif
    </div>

@endsection
