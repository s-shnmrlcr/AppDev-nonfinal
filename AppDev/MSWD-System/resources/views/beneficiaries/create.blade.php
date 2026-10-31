@extends('layouts.app')
@section('title', 'Add Beneficiary | MSWDO Kabacan')
@section('page-title', 'Add Beneficiary')
@section('breadcrumb', 'Beneficiaries / Add')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">MASTERLIST</div>
            <h1>Add Beneficiary</h1>
            <p>Enter the beneficiary's information for registration.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="form-layout"
          method="POST"
          action="{{ route('beneficiaries.store') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- PERSONAL INFORMATION --}}
        <section class="card form-section">
            <div class="section-heading">
                <h3>Personal Information</h3>
                <p>Basic identity and contact information.</p>
            </div>

            <div class="form-grid">
                <label>
                    First Name
                    <input name="first_name"
                           value="{{ old('first_name') }}"
                           autocomplete="given-name"
                           maxlength="255"
                           required>
                    @error('first_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Middle Name
                    <input name="middle_name"
                           value="{{ old('middle_name') }}"
                           autocomplete="additional-name"
                           maxlength="255">
                    @error('middle_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Last Name
                    <input name="last_name"
                           value="{{ old('last_name') }}"
                           autocomplete="family-name"
                           maxlength="255"
                           required>
                    @error('last_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Suffix
                    <input name="suffix"
                           value="{{ old('suffix') }}"
                           maxlength="30">
                    @error('suffix')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Date of Birth
                    <input name="date_of_birth"
                           type="date"
                           value="{{ old('date_of_birth') }}"
                           max="{{ now()->toDateString() }}"
                           required>
                    @error('date_of_birth')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Sex
                    <select name="sex" required>
                        <option value="" disabled @selected(!old('sex'))>
                            Select sex
                        </option>
                        @foreach (['Male', 'Female'] as $sex)
                            <option value="{{ $sex }}"
                                @selected(old('sex') === $sex)>
                                {{ $sex }}
                            </option>
                        @endforeach
                    </select>
                    @error('sex')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Civil Status
                    <select name="civil_status">
                        <option value="" @selected(old('civil_status', '') === '')>
                            Select civil status
                        </option>
                        @foreach (['Single', 'Married', 'Divorced', 'Widowed', 'Separated'] as $civilStatus)
                            <option value="{{ $civilStatus }}"
                                @selected(old('civil_status') === $civilStatus)>
                                {{ $civilStatus }}
                            </option>
                        @endforeach
                    </select>
                    @error('civil_status')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Contact Number
                    <input name="contact_number"
                           type="tel"
                           value="{{ old('contact_number') }}"
                           autocomplete="tel"
                           maxlength="30">
                    @error('contact_number')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        {{-- ADDRESS INFORMATION --}}
        <section class="card form-section">
            <div class="section-heading">
                <h3>Address Information</h3>
                <p>Specify the beneficiary's purok and barangay.</p>
            </div>

            <div class="form-grid">
                <label>
                    Street Address / Purok
                    <input name="purok"
                           value="{{ old('purok') }}"
                           autocomplete="address-line1"
                           maxlength="255">
                    @error('purok')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Barangay
                    <select name="barangay_id" required>
                        <option value="" disabled @selected(!old('barangay_id'))>
                            Select barangay
                        </option>
                        @foreach ($barangays as $barangay)
                            <option value="{{ $barangay->id }}"
                                @selected((string) old('barangay_id') === (string) $barangay->id)>
                                {{ $barangay->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('barangay_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        {{-- BENEFICIARY CLASSIFICATION --}}
        <section class="card form-section">
            <div class="section-heading">
                <h3>Beneficiary Classification</h3>
                <p>Classify the beneficiary for assistance processing.</p>
            </div>

            <div class="form-grid">
                {{-- SECTOR --}}
                <div class="form-field">
                    <label for="sector">Sector</label>

                    <select name="sector" id="sector" required>
                        <option value="" disabled @selected(!old('sector'))>
                            Select sector
                        </option>

                        @foreach ([
                            'General Beneficiary',
                            'Senior Citizen',
                            'PWD',
                            'Solo Parent',
                            'Pregnant/Lactating',
                            'Indigenous People',
                            'PWUD',
                            'Other'
                        ] as $sector)
                            <option value="{{ $sector }}"
                                @selected(old('sector') === $sector)>
                                {{ $sector }}
                            </option>
                        @endforeach
                    </select>

                    @error('sector')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    {{-- SHOW ONLY WHEN OTHER IS SELECTED --}}
                    <div id="other-sector-container"
                        class="conditional-field"
                        @if (old('sector') !== 'Other') hidden @endif>

                        <label for="other_sector">Specify Sector</label>

                        <input
                            type="text"
                            name="other_sector"
                            id="other_sector"
                            value="{{ old('other_sector') }}"
                            placeholder="Enter the beneficiary's sector"
                            maxlength="255"
                            @if (old('sector') === 'Other') required @endif
                        >

                        @error('other_sector')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- FAMILY HEAD --}}
                <div class="form-field">
                    <label for="is_familyhead">Is Family Head?</label>

                    <select name="is_familyhead"
                            id="is_familyhead"
                            required>
                        <option value="0"
                            @selected(old('is_familyhead', '0') == '0')>
                            No
                        </option>

                        <option value="1"
                            @selected(old('is_familyhead') == '1')>
                            Yes
                        </option>
                    </select>

                    @error('is_familyhead')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </section>

        {{-- FAMILY HEAD INFORMATION --}}
        <section class="card form-section"
                 id="family-head-section"
                 @if (old('is_familyhead', '0') != '1') hidden @endif>

            <div class="section-heading">
                <h3>Household &amp; Economic Information</h3>
                <p>
                    Provide household composition and economic details
                    for the registered family head.
                </p>
            </div>

            <div class="form-grid">
                <label>
                    Household Members
                    <input name="household_members"
                           type="number"
                           min="1"
                           step="1"
                           value="{{ old('household_members') }}"
                           data-family-required>
                    @error('household_members')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Dependents
                    <input name="dependents"
                           type="number"
                           min="0"
                           step="1"
                           value="{{ old('dependents') }}"
                           data-family-required>
                    @error('dependents')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Children
                    <input name="children"
                           type="number"
                           min="0"
                           step="1"
                           value="{{ old('children') }}"
                           data-family-required>
                    @error('children')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Senior Citizens in Household
                    <input name="senior_citizens"
                           type="number"
                           min="0"
                           step="1"
                           value="{{ old('senior_citizens') }}"
                           data-family-required>
                    @error('senior_citizens')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    PWD Members in Household
                    <input name="pwd_members"
                           type="number"
                           min="0"
                           step="1"
                           value="{{ old('pwd_members') }}"
                           data-family-required>
                    @error('pwd_members')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Occupation
                    <input name="occupation"
                           value="{{ old('occupation') }}"
                           maxlength="255"
                           data-family-required>
                    @error('occupation')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Source of Income
                    <input name="source_of_income"
                           value="{{ old('source_of_income') }}"
                           maxlength="255"
                           data-family-required>
                    @error('source_of_income')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Estimated Monthly Household Income (₱)
                    <input name="monthly_estimated_income"
                           type="number"
                           min="0"
                           step="0.01"
                           value="{{ old('monthly_estimated_income') }}"
                           data-family-required>
                    @error('monthly_estimated_income')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    Employment Status
                    <select name="employment_status" data-family-required>
                        <option value="">Select employment status</option>
                        @foreach ([
                            'Employed',
                            'Self-employed',
                            'Unemployed',
                            'Retired',
                            'Not applicable'
                        ] as $employmentStatus)
                            <option value="{{ $employmentStatus }}"
                                @selected(old('employment_status') === $employmentStatus)>
                                {{ $employmentStatus }}
                            </option>
                        @endforeach
                    </select>
                    @error('employment_status')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        {{-- IDENTIFICATION DOCUMENT --}}
        <section class="card form-section">
            <div class="section-heading">
                <h3>Identification Document</h3>
                <p>Optionally upload a document for identity verification.</p>
            </div>

            <div class="upload-box">
                <div class="upload-icon">↑</div>
                <strong>Upload ID document</strong>
                <span>JPG, JPEG, PNG or PDF up to 10 MB</span>
                <input name="id_document"
                       type="file"
                       accept=".jpg,.jpeg,.png,.pdf">
                @error('id_document')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </section>

        <div class="form-actions">
            <a href="{{ route('beneficiaries.index') }}"
               class="button button-secondary">
                Cancel
            </a>

            <button class="button button-primary" type="submit">
                Save Beneficiary
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const familyHeadSelect = document.getElementById('is_familyhead');
            const familyHeadSection = document.getElementById('family-head-section');
            const familyHeadFields = familyHeadSection.querySelectorAll('[data-family-required]');

            function toggleFamilyHeadFields() {
                const isFamilyHead = familyHeadSelect.value === '1';

                familyHeadSection.hidden = !isFamilyHead;

                familyHeadFields.forEach(function (field) {
                    field.required = isFamilyHead;
                    field.disabled = !isFamilyHead;
                });
            }

            familyHeadSelect.addEventListener('change', toggleFamilyHeadFields);

            toggleFamilyHeadFields();

            const sectorSelect = document.getElementById('sector');
            const otherSectorContainer = document.getElementById('other-sector-container');
            const otherSectorInput = document.getElementById('other_sector');

            function toggleOtherSector() {
                const isOther = sectorSelect.value === 'Other';

                otherSectorContainer.hidden = !isOther;
                otherSectorInput.required = isOther;
                otherSectorInput.disabled = !isOther;
            }

            sectorSelect.addEventListener('change', toggleOtherSector);

            toggleOtherSector();
        });
    </script>
@endsection
