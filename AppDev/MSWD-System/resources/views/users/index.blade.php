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
        </div><button class="button button-primary">+ Create User</button>
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
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user['name'] }}</strong></td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['role'] }}</td>
                            <td><x-status-badge :status="$user['status']" /></td>
                            <td><button class="table-action">Manage</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
