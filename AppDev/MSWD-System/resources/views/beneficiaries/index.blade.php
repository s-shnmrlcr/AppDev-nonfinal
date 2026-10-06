@extends('layouts.app')
@section('title', 'Beneficiaries | DSWD Portal')
@section('page-title', 'Beneficiaries')
@section('breadcrumb', 'Beneficiaries')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">MASTERLIST</div>
            <h1>Beneficiaries</h1>
            <p>Search, filter, verify, and manage registered family heads.</p>
        </div>
        <div class="button-group">
            <button class="button button-secondary">Import Masterlist</button>
            <button class="button button-secondary">Export</button>
            <a href="{{ route('beneficiaries.create') }}" class="button button-primary">+ Add Beneficiary</a>
        </div>
    </div>

    <section class="card">
        <div class="filter-toolbar">
            <div class="search-box"><span>⌕</span><input id="tableSearch" type="search"
                    placeholder="Search name, ID, household ID..."></div>
            <select>
                <option>All Barangays</option>
                @foreach (range(1, 17) as $i)
                    <option>Barangay {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
            <select>
                <option>All Sectors</option>
                <option>General Beneficiary</option>
                <option>Senior Citizen</option>
                <option>PWD</option>
                <option>Solo Parent</option>
            </select>
            <select>
                <option>All Statuses</option>
                <option>Verified</option>
                <option>Pending</option>
                <option>Rejected</option>
            </select>
        </div>

        <div class="table-meta"><span>8 sample records</span><span>Prototype data only</span></div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($beneficiaries as $b)
                        <tr>
                            <td class="mono">{{ $b['id'] }}</td>
                            <td><strong>{{ $b['name'] }}</strong></td>
                            <td>{{ $b['age'] }}</td>
                            <td>{{ $b['sex'] }}</td>
                            <td>{{ $b['barangay'] }}</td>
                            <td>{{ $b['sector'] }}</td>
                            <td>{{ $b['occupation'] }}</td>
                            <td><x-status-badge :status="$b['verification']" /></td>
                            <td><x-status-badge :status="$b['payout']" /></td>
                            <td><a class="table-action" href="{{ route('beneficiaries.show', $b['id']) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#beneficiaryTable tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
@endpush
