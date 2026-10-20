@extends('layouts.app')
@section('title', 'Edit Beneficiary | MSWDO Kabacan')
@section('page-title', 'Edit Beneficiary')
@section('breadcrumb', 'Beneficiaries / Edit')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">MASTERLIST</div>
            <h1>Edit Beneficiary</h1>
            <p>Update the beneficiary's information.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="form-layout" method="POST" action="{{ route('beneficiaries.update', $beneficiary) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="card form-section">
            <div class="section-heading">
                <h3>Personal Information</h3>
                <p>Basic identity and contact information.</p>
            </div>
            <div class="form-grid">
                <label>First Name<input name="first_name" value="{{ old('first_name', $beneficiary->first_name) }}" required></label>
                <label>Middle Name<input name="middle_name" value="{{ old('middle_name', $beneficiary->middle_name) }}"></label>
                <label>Last Name<input name="last_name" value="{{ old('last_name', $beneficiary->last_name) }}" required></label>
                <label>Suffix<input name="suffix" value="{{ old('suffix', $beneficiary->suffix) }}"></label>
                <label>Date of Birth<input name="date_of_birth" type="date"
                        value="{{ old('date_of_birth', $beneficiary->date_of_birth ? \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('Y-m-d') : '') }}" required></label>
                <label>Sex<select name="sex" required>
                        <option value="" disabled>Select sex</option>
                        @foreach (['Male', 'Female', 'Other'] as $sex)
                            <option value="{{ $sex }}" @selected(old('sex', $beneficiary->sex) === $sex)>{{ $sex }}</option>
                        @endforeach
                    </select></label>
                <label>Civil Status<select name="civil_status">
                        <option value="" disabled>Select civil status</option>
                        @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $civilStatus)
                            <option value="{{ $civilStatus }}" @selected(old('civil_status', $beneficiary->civil_status) === $civilStatus)>{{ $civilStatus }}</option>
                        @endforeach
                    </select></label>
                <label>Contact Number<input name="contact_number" type="tel" value="{{ old('contact_number', $beneficiary->contact_number) }}"></label>
                <label class="full">Address
                    <textarea name="address" rows="2">{{ old('address', $beneficiary->address) }}</textarea>
                </label>
                <label>Barangay<select name="barangay_id" required>
                        <option value="" disabled>Select barangay</option>
                        @foreach ($barangays as $barangay)
                            <option value="{{ $barangay->id }}" @selected((int) old('barangay_id', $beneficiary->barangay_id) === $barangay->id)>
                                {{ $barangay->name }}
                            </option>
                        @endforeach
                    </select></label>
                <label>Sector<select name="sector" required>
                        <option value="" disabled>Select sector</option>
                        @foreach (['General Beneficiary', 'Senior Citizen', 'PWD', 'Solo Parent', 'Pregnant/Lactating', 'Indigenous People', 'PWUD', 'Other'] as $sector)
                            <option value="{{ $sector }}" @selected(old('sector', $beneficiary->sector) === $sector)>{{ $sector }}</option>
                        @endforeach
                    </select></label>
            </div>
        </section>

        <section class="card form-section">
            <div class="section-heading">
                <h3>Household & Economic Information</h3>
                <p>Information used for eligibility assessment.</p>
            </div>
            <div class="form-grid">
                <label>Household ID<input name="household_id" value="{{ old('household_id', $beneficiary->household_id) }}"></label>
                <label>Household Members<input name="household_members" type="number" min="1" value="{{ old('household_members', $beneficiary->household_members) }}"></label>
                <label>Dependents<input name="dependents" type="number" min="0" value="{{ old('dependents', $beneficiary->dependents) }}"></label>
                <label>Children<input name="children" type="number" min="0" value="{{ old('children', $beneficiary->children) }}"></label>
                <label>Senior Citizens<input name="senior_citizens" type="number" min="0" value="{{ old('senior_citizens', $beneficiary->senior_citizens) }}"></label>
                <label>PWD Members<input name="pwd_members" type="number" min="0" value="{{ old('pwd_members', $beneficiary->pwd_members) }}"></label>
                <label>Occupation<input name="occupation" value="{{ old('occupation', $beneficiary->occupation) }}"></label>
                <label>Source of Income<input name="source_of_income" value="{{ old('source_of_income', $beneficiary->source_of_income) }}"></label>
                <label>Monthly Estimated Income<input name="monthly_income" type="number" min="0" step="0.01" value="{{ old('monthly_income', $beneficiary->monthly_income) }}"></label>
                <label>Employment Status<select name="employment_status">
                        <option value="" disabled>Select employment status</option>
                        @foreach (['Self-employed', 'Employed', 'Unemployed', 'Informal'] as $employmentStatus)
                            <option value="{{ $employmentStatus }}" @selected(old('employment_status', $beneficiary->employment_status) === $employmentStatus)>{{ $employmentStatus }}</option>
                        @endforeach
                    </select></label>
            </div>
        </section>

        <section class="card form-section">
            <div class="section-heading">
                <h3>Identification Document</h3>
                <p>Upload a new document only if you want to replace the current one.</p>
            </div>
            <div class="upload-box">
                <div class="upload-icon">↑</div><strong>Upload ID document</strong><span>JPG, JPEG, PNG or PDF up to 10 MB</span><input name="id_document" type="file" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </section>

        <div class="form-actions"><a href="{{ route('beneficiaries.index') }}"
                class="button button-secondary">Cancel</a><button class="button button-primary" type="submit">Save Changes</button>
        </div>
    </form>
@endsection