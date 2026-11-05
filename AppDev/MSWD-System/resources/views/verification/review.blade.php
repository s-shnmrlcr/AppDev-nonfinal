@extends('layouts.app')
@section('title', 'Identity Review | MSWDO Kabacan')
@section('page-title', 'Identity Review')
@section('breadcrumb', 'Verification Center / Review')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">AUDIT REVIEW</div>
            <h1>{{ $review->applicant_name ?: 'Applicant' }}</h1>
            <p>Review the saved applicant identity check and its submitted images.</p>
        </div>
        <div class="button-group">
            <a href="{{ route('audit-logs.index') }}" class="button button-secondary">Back to Audit Logs</a>
        </div>
    </div>

    <section class="card identity-review-record">
        <div class="identity-review-meta">
            <div>
                <span>Applicant</span>
                <strong>{{ $review->applicant_name ?: 'Name unavailable' }}</strong>
            </div>
            <div>
                <span>Decision</span>
                <x-status-badge :status="$review->decision === 'matched' ? 'Identity matches' : 'Identity does not match'" />
            </div>
            <div>
                <span>Reviewed</span>
                <time datetime="{{ \Illuminate\Support\Carbon::parse($review->created_at)->utc()->toIso8601String() }}"
                      data-local-datetime>{{ \Illuminate\Support\Carbon::parse($review->created_at)->format('M j, Y g:i A') }}</time>
            </div>
        </div>

        <div class="identity-review-images">
            <section class="identity-review-image">
                <h2>Captured claimant photo</h2>
                @if ($review->captured_photo_path)
                    <img src="{{ route('verification.logs.capture', $review->id) }}" alt="Captured claimant photo">
                @else
                    <p class="muted">No captured photo was saved for this older review.</p>
                @endif
            </section>
            <section class="identity-review-image">
                <h2>Submitted ID image</h2>
                @if ($review->id_image_path)
                    <img src="{{ route('verification.logs.id-image', $review->id) }}" alt="Submitted ID image">
                @else
                    <p class="muted">No ID image was saved for this older review.</p>
                @endif
            </section>
        </div>
    </section>
@endsection
