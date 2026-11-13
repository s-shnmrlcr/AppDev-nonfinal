@extends('layouts.app')
@section('title', 'Payout Releases | MSWDO Kabacan')
@section('page-title', 'Payout Releases')
@section('breadcrumb', 'Payout Releases')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">RELEASE CONTROL</div>
            <h1>Payout Releases</h1>
            <p>Review payout release records.</p>
        </div>
        <a href="{{ route('payouts.export') }}" class="button button-primary">Export Releases CSV</a>
    </div>
    <section class="card">
        <div class="filter-toolbar">
            {{-- Search by reference, beneficiary, barangay, or program --}}
            <div class="search-box">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m16 16 5 5" />
                </svg>

                <input
                    id="tableSearch"
                    type="search"
                    aria-label="Search payout releases"
                    placeholder="Search payout releases..."
                >
            </div>

            {{-- Filter by payout program --}}
            <select id="programFilter" aria-label="Filter by program">
                <option value="">All Programs</option>

                @foreach ($programs as $program)
                    <option value="{{ $program->program_name }}">
                        {{ $program->program_name }}
                    </option>
                @endforeach
            </select>

            {{-- Filter by payout status --}}
            <select id="statusFilter" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Partially Released">Partially Released</option>
                <option value="Released">Released</option>
            </select>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="payoutTable">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Beneficiary</th>
                        <th>Barangay</th>
                        <th>Program</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payouts as $p)
                        <tr data-payout data-program="{{ $p['program'] }}" data-status="{{ $p['status'] }}">
                            <td class="mono">{{ $p['ref'] }}</td>
                            <td><strong>{{ $p['beneficiary'] }}</strong></td>
                            <td>{{ $p['barangay'] }}</td>
                            <td>{{ $p['program'] }}</td>
                            <td>{{ $p['amount'] }}</td>
                            <td><x-status-badge :status="$p['status']" /></td>
                            {{-- action to view releases -shijme --}}
                            <td>
                                <a href="{{ route('payout-releases.index', $p['id']) }}" class="button button-secondary">
                                    View Releases
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state table-empty-state">
                                    <strong>No payout records</strong>
                                    <p>Payout records will appear here when they are available.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @push('scripts')
    <script>
        // Listen for changes in the search field and dropdown filters
        document.getElementById('tableSearch')?.addEventListener('input', filterPayouts);
        document.getElementById('programFilter')?.addEventListener('change', filterPayouts);
        document.getElementById('statusFilter')?.addEventListener('change', filterPayouts);

        function filterPayouts() {
            const query = document.getElementById('tableSearch').value.toLowerCase();
            const program = document.getElementById('programFilter').value;
            const status = document.getElementById('statusFilter').value;

            // Combine all selected filters
            document.querySelectorAll('#payoutTable tbody tr[data-payout]').forEach(row => {

                const matches =
                    row.innerText.toLowerCase().includes(query) &&
                    (!program || row.dataset.program === program) &&
                    (!status || row.dataset.status === status);

                row.hidden = !matches;
            });
        }
    </script>
    @endpush
@endsection
