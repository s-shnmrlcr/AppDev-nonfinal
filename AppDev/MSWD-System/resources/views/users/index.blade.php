@extends('layouts.app')
@section('title', 'User Management | DSWD Portal')
@section('page-title', 'User Management')
@section('breadcrumb', 'User Management')

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">ACCESS CONTROL</div>
            <h1>User Management</h1>
            <p>Manage authorized personnel and role-based permissions.</p>
        </div>
    </div>
    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>Authorized staff</td>
                            <td><x-status-badge status="Active" /></td>
                            <td><span class="muted">—</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state table-empty-state">
                                    <strong>No user accounts</strong>
                                    <p>Registered accounts will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
