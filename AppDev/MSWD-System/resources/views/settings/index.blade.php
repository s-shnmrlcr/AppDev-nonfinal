@extends('layouts.app')
@section('title', 'Settings | DSWD Portal')
@section('page-title', 'Settings')
@section('breadcrumb', 'Settings')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">SYSTEM CONFIGURATION</div>
            <h1>Settings</h1>
            <p>Review organization settings and payout rules.</p>
        </div>
    </div>
    <div class="settings-layout">
        <section class="card form-section">
            <div class="section-heading">
                <h3>System Settings</h3>
                <p>General portal configuration.</p>
            </div>
            <div class="form-stack"><label>System name<input value="{{ $settings['system_name'] }}" readonly></label><label>Authorized
                    email domain<input value="{{ $settings['email_domain'] }}" readonly><small>Contact an administrator to update organization settings.</small></label><label>Barangays<input
                        value="{{ $settings['barangays'] }}" disabled></label></div>
        </section>
        <section class="card form-section">
            <div class="section-heading">
                <h3>Payout Rules</h3>
                <p>Required controls before a payout can be released.</p>
            </div>
            <div class="settings-list"><label><input type="checkbox" checked disabled> One assistance per
                    beneficiary</label><label><input type="checkbox" checked disabled> One barangay per
                    beneficiary</label><label><input type="checkbox" checked disabled> Verification before
                    release</label><label><input type="checkbox" checked disabled> Complete requirements before
                    release</label><label><input type="checkbox" checked disabled> Duplicate checking</label><label><input
                        type="checkbox" checked disabled> Audit important actions</label></div>
        </section>
    </div>
@endsection
