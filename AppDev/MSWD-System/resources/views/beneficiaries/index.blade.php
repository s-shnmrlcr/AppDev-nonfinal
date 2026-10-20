@extends('layouts.app')
@section('title', 'Beneficiaries | MSWDO Kabacan')
@section('page-title', 'Beneficiaries')
@section('breadcrumb', 'Beneficiaries')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">MASTERLIST</div>
            <h1>Beneficiaries</h1>
            <p>Search and manage registered beneficiaries.</p>
        </div>
        <div class="button-group">
            <form method="POST" action="{{ route('beneficiaries.import') }}" enctype="multipart/form-data" class="inline-import-form">
                @csrf
                <label class="button button-secondary import-label" for="masterlistFile">Import CSV</label>
                <input id="masterlistFile" name="masterlist" type="file" accept=".csv,.txt,text/csv" required>
                <button class="button button-secondary" type="submit">Upload</button>
            </form>
            <a href="{{ route('beneficiaries.export') }}" class="button button-secondary">Export CSV</a>
            <a href="{{ route('beneficiaries.create') }}" class="button button-primary">+ Add Beneficiary</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    <p class="import-help">CSV columns: first_name, middle_name, last_name, suffix, date_of_birth, sex, civil_status, contact_number, address, barangay, sector, household_id, household_members, dependents, children, senior_citizens, pwd_members, occupation, source_of_income, monthly_income, employment_status. Required: first_name, last_name, date_of_birth, sex, barangay, sector.</p>

    <section class="card">
        <div class="filter-toolbar">
            <div class="search-box"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m16 16 5 5" /></svg><input id="tableSearch" type="search"
                    aria-label="Search beneficiaries"></div>
            <select id="barangayFilter">
                <option value="">All Barangays</option>
                @foreach ( $barangays as $barangay )
                    <option value="{{ $barangay->name }}">{{ $barangay->name }}</option>
                @endforeach
            </select>
            <select id="sectorFilter" aria-label="Filter by sector">
                <option>All Sectors</option>
                <option>General Beneficiary</option>
                <option>Senior Citizen</option>
                <option>PWD</option>
                <option>Solo Parent</option>
                <option>Pregnant/Lactating</option>
                <option>Indigenous People</option>
                <option>PWUD</option>
                <option>Other</option>
            </select>
            <select id="verificationFilter" aria-label="Filter by verification status">
                <option>All Statuses</option>
                <option>Verified</option>
                <option>Pending</option>
                <option>Rejected</option>
            </select>
        </div>

        <div class="table-scroll">
            <table class="data-table" id="beneficiaryTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Barangay</th>
                        <th>Sector</th>
                        <th>Occupation</th>
                        <th>Verification</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beneficiaries as $b)
                        <tr data-beneficiary data-barangay="{{ $b['barangay'] }}" data-sector="{{ $b['sector'] }}" data-verification="{{ $b['verification'] }}">
                            <td class="mono">{{ $b['id'] }}</td>
                            <td><strong>{{ $b['name'] }}</strong></td>
                            <td>{{ $b['age'] }}</td>
                            <td>{{ $b['sex'] }}</td>
                            <td>{{ $b['barangay'] }}</td>
                            <td>{{ $b['sector'] }}</td>
                            <td>{{ $b['occupation'] }}</td>
                            <td><x-status-badge :status="$b['verification']" /></td>
                            <td><x-status-badge :status="$b['payout']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state table-empty-state">
                                    <strong>No beneficiaries yet</strong>
                                    <p>Registered beneficiary records will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('input', function() {
            filterBeneficiaries();
        });
        document.getElementById('barangayFilter')?.addEventListener('change', filterBeneficiaries);
        document.getElementById('sectorFilter')?.addEventListener('change', filterBeneficiaries);
        document.getElementById('verificationFilter')?.addEventListener('change', filterBeneficiaries);

        function filterBeneficiaries() {
            const query = document.getElementById('tableSearch').value.toLowerCase();
            const barangay = document.getElementById('barangayFilter').value.toLowerCase();
            const sector = document.getElementById('sectorFilter').value;
            const verification = document.getElementById('verificationFilter').value;
            // I-combine ang tanang filter aron kinahanglan motakdo ang beneficiary sa matag napiling criteria. -shaine
            document.querySelectorAll('#beneficiaryTable tbody tr[data-beneficiary]').forEach(row => {
                const matches = row.innerText.toLowerCase().includes(query)
                    && (!barangay || row.dataset.barangay.toLowerCase() === barangay)
                    && (sector === 'All Sectors' || row.dataset.sector === sector)
                    && (verification === 'All Statuses' || row.dataset.verification === verification);
                row.hidden = !matches;
            });
        }
    </script>
@endpush
