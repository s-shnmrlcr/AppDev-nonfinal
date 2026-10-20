@extends('layouts.app')
@section('title', 'Edit Program | MSWDO Kabacan')
@section('page-title', 'Edit Program')
@section('breadcrumb', 'Payout Programs / Edit')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>Edit Program</h1>
            <p>Update the payout program details.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="form-layout" method="POST" action="{{ route('programs.update', $program) }}">
        @csrf
        @method('PUT')
        <section class="card form-section">
            <div class="form-grid">
                <label>Program Name<input name="name" value="{{ old('name', $program->name) }}" required></label>
                <label>Type<select name="type" required>
                        @foreach (['Financial Assistance', 'Emergency Aid', 'In-kind Assistance', 'Other'] as $type)
                            <option value="{{ $type }}" @selected(old('type', $program->type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select></label>
                <label>Amount<input name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $program->amount) }}" required></label>
                <label>Coverage<input name="coverage" value="{{ old('coverage', $program->coverage) }}" required></label>
                <label>End Date<input name="end_date" type="date" value="{{ old('end_date', $program->end_date?->format('Y-m-d')) }}"></label>
                <label>Status<select name="status" required>
                        @foreach (['Draft', 'Active'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $program->status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select></label>
            </div>
        </section>

        <div class="form-actions">
            <a href="{{ route('programs.index') }}" class="button button-secondary">Cancel</a>
            <button class="button button-primary" type="submit">Save Changes</button>
        </div>
    </form>
@endsection