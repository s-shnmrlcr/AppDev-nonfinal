@extends('layouts.app')
@section('title', 'Claimant Identity Check | MSWDO Kabacan')
@section('page-title', 'Claimant Identity Check')
@section('breadcrumb', 'Verification Center')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">IN-PERSON CLAIM</div>
            <h1>Claimant Identity Check</h1>
            <p>Compare the claimant’s face with the portrait on their uploaded ID.</p>
            <p class="identity-privacy">On-device face comparison is advisory only. Staff must make the final decision. When a review is submitted, the captured photo and selected ID image are saved in restricted storage and retained with the audit record.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <section class="card identity-check-card"
             id="identityCheck"
             data-model-path="{{ asset('models/face-api') }}">
        <div class="identity-panels">
            <section class="identity-panel">
                <div class="section-heading">
                    <h3>Live claimant</h3>
                </div>
                <div class="camera-frame">
                    <video id="claimantCamera" autoplay muted playsinline aria-label="Live claimant camera preview"></video>
                    <canvas id="claimantCapture" class="hidden" aria-label="Captured claimant photo"></canvas>
                    <div id="cameraPrompt" class="camera-prompt">
                        <span class="empty-state-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 7h3l2-3h6l2 3h3v13H4z" /><circle cx="12" cy="13" r="4" /></svg>
                        </span>
                        <span>Camera preview</span>
                    </div>
                </div>
                <div class="camera-controls">
                    <button type="button" class="button button-secondary" id="startCamera">Start camera</button>
                    <button type="button" class="button button-primary" id="capturePhoto" disabled>Capture photo</button>
                    <button type="button" class="button button-secondary" id="retakePhoto" hidden>Retake photo</button>
                </div>
                <p id="cameraStatus" class="camera-status" role="status"></p>
            </section>

            <section class="identity-panel">
                <div class="section-heading">
                    <h3>Photo ID</h3>
                </div>
                <label class="id-upload">
                    <input type="file" id="idDocument" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <span class="empty-state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="9" cy="11" r="2" /><path d="m5 17 5-4 3 3 2-2 4 3" /></svg>
                    </span>
                    <strong>Select uploaded ID document</strong>
                    <span>JPG or PNG · up to 10 MB</span>
                </label>
                <div class="id-preview-frame">
                    <img id="idPreview" class="hidden" alt="Selected ID image preview">
                    <p id="idPrompt">ID document preview</p>
                </div>
                <p id="idStatus" class="camera-status" role="status"></p>
            </section>
        </div>

        <div class="identity-match-advisory" aria-live="polite">
            <strong>Automated comparison</strong>
            <p id="matchStatus">Capture a photo and select a JPG or PNG ID image to compare faces.</p>
            <small>A match score is not proof of identity or liveness and has not been calibrated for every ID or photo. Review both images yourself.</small>
        </div>

        <form method="POST" action="{{ route('verification.review') }}" id="identityReviewForm" class="identity-review-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="decision" id="reviewDecision" value="">
            <input type="file" name="captured_photo" id="capturedPhotoUpload" hidden>
            <input type="file" name="id_image" id="idImageUpload" hidden>
            <label class="claimant-select-label" for="beneficiaryId">
                Applicant
                <select name="beneficiary_id" id="beneficiaryId" required>
                    <option value="">Select the registered applicant</option>
                    @foreach ($beneficiaries as $beneficiary)
                        <option value="{{ $beneficiary->id }}"
                            @selected((string) old('beneficiary_id') === (string) $beneficiary->id)>
                            BEN-{{ str_pad((string) $beneficiary->id, 6, '0', STR_PAD_LEFT) }} — {{ $beneficiary->full_name }}
                        </option>
                    @endforeach
                </select>
                @if ($beneficiaries->isEmpty())
                    <small>No registered beneficiaries are available. Add a beneficiary before recording an identity review.</small>
                @endif
            </label>
            <div class="button-group">
                <button type="submit" name="decision" value="matched" class="button button-primary" disabled>
                    Identity matches
                </button>
                <button type="submit" name="decision" value="not_matched" class="button button-secondary" disabled>
                    Identity does not match
                </button>
            </div>
            <p id="reviewStatus" class="camera-status" role="status">Capture a photo and select an ID image to enable review.</p>
        </form>
    </section>
@endsection
