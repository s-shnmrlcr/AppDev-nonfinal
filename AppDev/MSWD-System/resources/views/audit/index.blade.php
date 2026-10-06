@extends('layouts.app')
@section('title', 'Audit Logs | DSWD Portal')
@section('page-title', 'Audit Logs')
@section('breadcrumb', 'Audit Logs')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">TRACEABILITY</div>
            <h1>Audit Logs</h1>
            <p>Every important system action is recorded for review.</p>
        </div>
    </div>
    <section class="card">
        <div class="filter-toolbar">
            <div class="search-box"><span>⌕</span><input type="search" placeholder="Search user, action, beneficiary...">
            </div><select>
                <option>All modules</option>
                <option>Beneficiaries</option>
                <option>Verification</option>
                <option>Payouts</option>
            </select><select>
                <option>All statuses</option>
                <option>Success</option>
                <option>Failed</option>
            </select>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Target</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log['time'] }}</td>
                            <td><strong>{{ $log['user'] }}</strong></td>
                            <td>{{ $log['role'] }}</td>
                            <td>{{ $log['action'] }}</td>
                            <td>{{ $log['module'] }}</td>
                            <td class="mono">{{ $log['target'] }}</td>
                            <td><x-status-badge :status="$log['status']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
