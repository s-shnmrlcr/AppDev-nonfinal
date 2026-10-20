@extends('layouts.app')
@section('title', 'Create Program | DSWD Portal')
@section('page-title', 'Create Program')
@section('breadcrumb', 'Payout Programs / Create')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>Create Payout Program</h1>
            <p>Enter the assistance details for this program.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('programs.store') }}" class="form-layout">
        @csrf
        <section class="card form-section">
            <div class="form-grid">
                <label>Program name<input name="name" value="{{ old('name') }}" required maxlength="255"></label>
                <label>Program type
                    <select name="type" required>
                        <option value="" disabled @selected(old('type') === null)>Select program type</option>
                        @foreach (['Financial Assistance', 'Emergency Aid', 'In-kind Assistance', 'Other'] as $type)
                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Assistance amount<input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required></label>
                <label>Coverage<input name="coverage" value="{{ old('coverage') }}" required maxlength="255"></label>
                <label>End date<input name="end_date" type="date" value="{{ old('end_date') }}"></label>
                <label>Status
                    <select name="status" required>
                        <option value="Draft" @selected(old('status', 'Draft') === 'Draft')>Draft</option>
                        <option value="Active" @selected(old('status') === 'Active')>Active</option>
                    </select>
                </label>
            </div>
        </section>
        <div class="form-actions">
            <a href="{{ route('programs.index') }}" class="button button-secondary">Cancel</a>
            <button type="submit" class="button button-primary">Save Program</button>
        </div>
    </form>
@endsection
