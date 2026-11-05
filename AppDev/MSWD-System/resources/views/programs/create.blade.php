@extends('layouts.app')
@php($isEditing = isset($program))
@section('title', ($isEditing ? 'Edit' : 'Create').' Program | MSWDO Kabacan')
@section('page-title', $isEditing ? 'Edit Program' : 'Create Program')
@section('breadcrumb', $isEditing ? 'Payout Programs / Edit' : 'Payout Programs / Create')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ASSISTANCE MANAGEMENT</div>
            <h1>{{ $isEditing ? 'Edit Payout Program' : 'Create Payout Program' }}</h1>
            <p>{{ $isEditing ? 'Update the assistance details for this program.' : 'Enter the assistance details for this program.' }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $isEditing ? route('programs.update', $program) : route('programs.store') }}"
          class="form-layout">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif
        <section class="card form-section">
            <div class="form-grid">
                <label>Program name<input name="program_name" value="{{ old('program_name', $program->program_name ?? '') }}" required maxlength="255"></label>
                <label class="full">Description<textarea name="description" rows="4">{{ old('description', $program->description ?? '') }}</textarea></label>
                <label>Program budget<input name="budget" type="number" min="0.01" step="0.01" value="{{ old('budget', $program->budget ?? '') }}" required></label>
                <label>Start date<input name="start_date" type="date" value="{{ old('start_date', isset($program) ? $program->start_date?->format('Y-m-d') : '') }}"></label>
                <label>End date<input name="end_date" type="date" value="{{ old('end_date', isset($program) ? $program->end_date?->format('Y-m-d') : '') }}"></label>
                <label>Status
                    <select name="status" required>
                        <option value="Draft" @selected(old('status', $program->status ?? 'Draft') === 'Draft')>Draft</option>
                        <option value="Active" @selected(old('status', $program->status ?? 'Draft') === 'Active')>Active</option>
                    </select>
                </label>
            </div>
        </section>
        <div class="form-actions">
            <a href="{{ route('programs.index') }}" class="button button-secondary">Cancel</a>
            <button type="submit" class="button button-primary">{{ $isEditing ? 'Update Program' : 'Save Program' }}</button>
        </div>
    </form>
@endsection
