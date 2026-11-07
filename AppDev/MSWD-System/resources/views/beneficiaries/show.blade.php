@extends('layouts.app')
@section('title', 'Beneficiary Details | MSWDO Kabacan')
@section('page-title', 'Beneficiary Details')
@section('breadcrumb', 'Beneficiaries / View')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">BEN-{{ str_pad((string) $beneficiary->id, 6, '0', STR_PAD_LEFT) }}</div>
            <h1>{{ $beneficiary->full_name }}</h1>
            <p>Review the beneficiary information and verification documents.</p>
        </div>
        <div class="button-group">
            <a href="{{ route('beneficiaries.index') }}" class="button button-secondary">Back to Beneficiaries</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    {{-- EN: Show the beneficiary's saved personal details as read-only; TL: Ipakita ang naka-save na detalye nang hindi nae-edit; Bisaya: Ipakita ang na-save nga personal nga detalye nga dili ma-edit. -shaine --}}
    <section class="card form-section">
        <div class="section-heading">
            <h3>Personal Information</h3>
        </div>
        <div class="detail-grid">
            <div><span>First Name</span><strong>{{ $beneficiary->first_name }}</strong></div>
            <div><span>Middle Name</span><strong>{{ $beneficiary->middle_name ?: '—' }}</strong></div>
            <div><span>Last Name</span><strong>{{ $beneficiary->last_name }}</strong></div>
            <div><span>Suffix</span><strong>{{ $beneficiary->suffix ?: '—' }}</strong></div>
            <div><span>Date of Birth</span><strong>{{ $beneficiary->date_of_birth->format('F j, Y') }} ({{ $beneficiary->date_of_birth->age }} years old)</strong></div>
            <div><span>Sex</span><strong>{{ $beneficiary->sex }}</strong></div>
            <div><span>Civil Status</span><strong>{{ $beneficiary->civil_status ?: '—' }}</strong></div>
            <div><span>Contact Number</span><strong>{{ $beneficiary->contact_number ?: '—' }}</strong></div>
            <div><span>Purok</span><strong>{{ $beneficiary->purok ?: '—' }}</strong></div>
            <div><span>Barangay</span><strong>{{ $beneficiary->barangay->name }}</strong></div>
            <div><span>Sector</span><strong>{{ $beneficiary->sector }}</strong></div>
            <div><span>Family Head</span><strong>{{ $beneficiary->is_familyhead ? 'Yes' : 'No' }}</strong></div>
        </div>
    </section>

    @if ($beneficiary->familyHead)
        {{-- EN: Display extra household fields only for family heads; TL: Ipakita lang ang household fields para sa family head; Bisaya: Ipakita lang ang household fields kon family head. -shaine --}}
        <section class="card form-section">
            <div class="section-heading">
                <h3>Household Information</h3>
            </div>
            <div class="detail-grid">
                <div><span>Household Members</span><strong>{{ $beneficiary->familyHead->household_members ?? '—' }}</strong></div>
                <div><span>Dependents</span><strong>{{ $beneficiary->familyHead->dependents ?? '—' }}</strong></div>
                <div><span>Children</span><strong>{{ $beneficiary->familyHead->children ?? '—' }}</strong></div>
                <div><span>Senior Citizens</span><strong>{{ $beneficiary->familyHead->senior_citizens ?? '—' }}</strong></div>
                <div><span>PWD Members</span><strong>{{ $beneficiary->familyHead->pwd_members ?? '—' }}</strong></div>
                <div><span>Occupation</span><strong>{{ $beneficiary->familyHead->occupation ?: '—' }}</strong></div>
                <div><span>Source of Income</span><strong>{{ $beneficiary->familyHead->source_of_income ?: '—' }}</strong></div>
                <div><span>Monthly Estimated Income</span><strong>{{ $beneficiary->familyHead->monthly_estimated_income !== null ? number_format((float) $beneficiary->familyHead->monthly_estimated_income, 2) : '—' }}</strong></div>
                <div><span>Employment Status</span><strong>{{ $beneficiary->familyHead->employment_status ?: '—' }}</strong></div>
            </div>
        </section>
    @endif

    {{-- EN: Provide the application review checklist and decision form;
         TL: Ibigay ang checklist at form ng desisyon sa application review;
         Bisaya: Ihatag ang checklist ug porma sa desisyon sa application review. --}}
    <section class="card form-section">
        <div class="section-heading">
            <h3>Application Review</h3>
            <p>
                Current status:
                <x-status-badge :status="$beneficiary->verification_status" />
                {{-- Show remarks from the completed application review, if any. --}}
                @if ($beneficiary->review_remarks)
                    <div class="alert alert-info">
                        <strong>Review Remarks:</strong>
                        <p>{{ $beneficiary->review_remarks }}</p>
                    </div>
                @endif
            </p>
        </div>

        {{-- Show the uploaded ID document, if it is available. --}}
        <div class="detail-grid">
            <div>
                <span>Identification Document</span>
                <strong>
                    @if ($hasIdDocument)
                        <a
                            href="{{ route('beneficiaries.document', $beneficiary) }}"
                            target="_blank"
                            rel="noopener"
                        >
                            View uploaded ID document
                        </a>
                    @else
                        Not uploaded or unavailable
                    @endif
                </strong>
            </div>
        </div>

        {{-- Allow staff to review pending applications and applications with corrections. --}}
        @if (in_array($beneficiary->verification_status, ['Pending', 'Needs Correction']))
            <form
                method="POST"
                action="{{ route('beneficiaries.update-verification', $beneficiary) }}"
                id="beneficiary-review-form"
                class="review-form"
                onsubmit="return confirm('Are you sure you want to submit this application review decision?')"
            >

                @csrf
                @method('PATCH')

                {{-- Checklist: each item must be acknowledged before submitting. --}}
                <div class="review-checklist-wrap">
                    <h4>Review Checklist</h4>
                    <p class="muted">
                        Confirm that you have reviewed each applicable part of the application.
                    </p>

                    <div class="review-checklist">
                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="personal_info_checked"
                                value="1"
                                required
                                @checked(old('personal_info_checked'))
                            >
                            <span>I reviewed the beneficiary's personal information.</span>
                        </label>

                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="address_checked"
                                value="1"
                                required
                                @checked(old('address_checked'))
                            >
                            <span>I checked the address, purok, and barangay information.</span>
                        </label>

                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="classification_checked"
                                value="1"
                                required
                                @checked(old('classification_checked'))
                            >
                            <span>I reviewed the beneficiary's sector and household information, if applicable.</span>
                        </label>

                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                name="documents_checked"
                                value="1"
                                required
                                @checked(old('documents_checked'))
                            >
                            <span>I checked the uploaded document for readability and consistency with the application.</span>
                        </label>
                    </div>
                </div>

                {{-- Let staff choose the application review decision. --}}
                <div class="form-group review-form-row">
                    <label for="verification_status">Review Decision</label>
                    <select
                        name="verification_status"
                        id="verification_status"
                        class="review-select"
                        required
                    >
                        <option value="">Select a decision</option>
                        <option
                            value="Verified"
                            @selected(old('verification_status') === 'Verified')
                        >
                            Verified
                        </option>
                        <option
                            value="Needs Correction"
                            @selected(old('verification_status') === 'Needs Correction')
                        >
                            Needs Correction
                        </option>
                        <option
                            value="Rejected"
                            @selected(old('verification_status') === 'Rejected')
                        >
                            Rejected
                        </option>
                    </select>
                </div>

                {{-- Remarks explain corrections or rejection; JavaScript toggles required,
                     while the controller also enforces this rule server-side. --}}
                <div class="form-group review-form-row">
                    <label for="remarks">
                        Review Remarks
                        <span class="muted">(Required for Needs Correction or Rejected)</span>
                    </label>
                    <textarea
                        name="remarks"
                        id="remarks"
                        class="review-textarea"
                        rows="4"
                        maxlength="2000"
                        placeholder="Explain what needs to be corrected or why the application is rejected."
                    >{{ old('remarks') }}</textarea>
                </div>

                {{-- Display validation messages for individual form fields. --}}
                @foreach ([
                    'personal_info_checked',
                    'address_checked',
                    'classification_checked',
                    'documents_checked',
                    'verification_status',
                    'remarks'
                ] as $field)
                    @error($field)
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                @endforeach

                <div class="review-actions">
                    <button
                        type="submit"
                        class="button button-primary review-submit"
                    >
                        Submit Review
                    </button>
                </div>
            </form>

            {{-- Make remarks required in the browser for correction/rejection decisions. --}}
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const decision = document.getElementById('verification_status');
                    const remarks = document.getElementById('remarks');

                    function updateRemarksRequirement() {
                        const requiresRemarks = [
                            'Needs Correction',
                            'Rejected'
                        ].includes(decision.value);

                        remarks.required = requiresRemarks;
                    }

                    decision.addEventListener('change', updateRemarksRequirement);
                    updateRemarksRequirement();
                });
            </script>
        @else
            {{-- Reviewed applications cannot be changed through this form. --}}
            <p class="muted">
                This beneficiary's application review has already been completed.
            </p>
        @endif
    </section>
@endsection
