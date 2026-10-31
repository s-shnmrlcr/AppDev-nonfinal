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

    {{-- EN: Require an available ID, document-check acknowledgment, and confirmation before updating; TL: Kailangan ang ID, kumpirmasyon ng pag-check, at final confirmation; Bisaya: Kinahanglan ang ID, kumpirmasyon sa pagsusi, ug final nga pag-uyon. -shaine --}}
    <section class="card form-section">
        <div class="section-heading">
            <h3>Verification</h3>
            <p>Current status: <x-status-badge :status="$beneficiary->verification_status" /></p>
        </div>
        <div class="detail-grid">
            <div>
                <span>Identification Document</span>
                <strong>
                    @if ($hasIdDocument)
                        <a href="{{ route('beneficiaries.document', $beneficiary) }}" target="_blank" rel="noopener">View uploaded ID document</a>
                    @else
                        Not uploaded or unavailable
                    @endif
                </strong>
            </div>
        </div>

        @if ($beneficiary->verification_status === 'Pending')
            @if ($hasIdDocument)
                <form method="POST" action="{{ route('beneficiaries.update-verification', $beneficiary) }}" class="form-actions" onsubmit="return confirm('Confirm that you reviewed the beneficiary information and checked all required documents before marking this beneficiary as Verified?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="verification_status" value="Verified">
                    <label class="checkbox-label">
                        <input type="checkbox" name="documents_checked" value="1" required @checked(old('documents_checked'))>
                        I have reviewed the beneficiary information and checked all required documents.
                    </label>
                    <button type="submit" class="button button-primary">Mark as Verified</button>
                </form>
            @else
                <p class="muted">An available ID document is required before this beneficiary can be verified.</p>
            @endif
        @else
            <p class="muted">This beneficiary's verification status can no longer be changed from this action.</p>
        @endif
    </section>
@endsection
