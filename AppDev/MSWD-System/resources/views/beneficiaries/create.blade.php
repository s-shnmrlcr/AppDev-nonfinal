@extends('layouts.app')
@section('title', 'Add Beneficiary | DSWD Portal')
@section('page-title', 'Add Beneficiary')
@section('breadcrumb', 'Beneficiaries / Add')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">MASTERLIST</div>
            <h1>Add Beneficiary</h1>
            <p>Register a family head using fictional prototype information.</p>
        </div>
    </div>

    <form class="form-layout"
        onsubmit="event.preventDefault(); alert('Prototype only: beneficiary record would be validated and saved here.');">
        <section class="card form-section">
            <div class="section-heading">
                <h3>Personal Information</h3>
                <p>Basic identity and contact information.</p>
            </div>
            <div class="form-grid">
                <label>First Name<input required placeholder="Juan"></label>
                <label>Middle Name<input placeholder="Santos"></label>
                <label>Last Name<input required placeholder="Dela Cruz"></label>
                <label>Suffix<input placeholder="Jr."></label>
                <label>Date of Birth<input type="date" required></label>
                <label>Sex<select>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select></label>
                <label>Civil Status<select>
                        <option>Single</option>
                        <option>Married</option>
                        <option>Widowed</option>
                        <option>Separated</option>
                    </select></label>
                <label>Contact Number<input placeholder="09XX XXX XXXX"></label>
                <label class="full">Address
                    <textarea rows="2" placeholder="House / street / municipality"></textarea>
                </label>
                <label>Barangay<select required>
                        @foreach ($barangays as $barangay)
                            <option>{{ $barangay }}</option>
                        @endforeach
                    </select></label>
                <label>Sector<select>
                        <option>General Beneficiary</option>
                        <option>Senior Citizen</option>
                        <option>PWD</option>
                        <option>Solo Parent</option>
                        <option>Pregnant/Lactating</option>
                        <option>Indigenous People</option>
                        <option>PWUD</option>
                        <option>Other</option>
                    </select></label>
            </div>
        </section>

        <section class="card form-section">
            <div class="section-heading">
                <h3>Household & Economic Information</h3>
                <p>Information used for eligibility assessment.</p>
            </div>
            <div class="form-grid">
                <label>Household ID<input placeholder="HH-2026-00001"></label>
                <label>Household Members<input type="number" min="1" placeholder="4"></label>
                <label>Dependents<input type="number" min="0" placeholder="3"></label>
                <label>Children<input type="number" min="0" placeholder="2"></label>
                <label>Senior Citizens<input type="number" min="0" placeholder="0"></label>
                <label>PWD Members<input type="number" min="0" placeholder="0"></label>
                <label>Occupation<input placeholder="Driver"></label>
                <label>Source of Income<input placeholder="Daily wage"></label>
                <label>Monthly Estimated Income<input type="number" placeholder="8000"></label>
                <label>Employment Status<select>
                        <option>Self-employed</option>
                        <option>Employed</option>
                        <option>Unemployed</option>
                        <option>Informal</option>
                    </select></label>
            </div>
        </section>

        <section class="card form-section">
            <div class="section-heading">
                <h3>Identification Document</h3>
                <p>Upload a document for later identity verification.</p>
            </div>
            <div class="upload-box">
                <div class="upload-icon">↑</div><strong>Drop a document here</strong><span>JPG, JPEG, PNG or PDF up to 10
                    MB</span><input type="file" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </section>

        <div class="form-actions"><a href="{{ route('beneficiaries.index') }}"
                class="button button-secondary">Cancel</a><button class="button button-primary">Save Beneficiary</button>
        </div>
    </form>
@endsection
