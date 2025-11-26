@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
{{-- NOTE: Assuming you have Font Awesome Pro configured or use the free version. The icons below use 'fal' (light) or 'far' (regular) for a more minimal look. If you don't have it, change 'fal' to 'fas'. --}}
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" xintegrity="sha384-AYmEC3Yw5U5dD7i7t53X30m46O7hF6+I23g9M+B4hL2t8F9gYt5Tz30O+gR7/d6KxYj" crossorigin="anonymous">


<div class="mtk-app">

    {{-- SIDEBAR --}}
    <aside class="mtk-sidebar">
        <div class="mtk-brand">
           <h1 class="mtk-brand-title">MediTrack</h1>
        </div>

        <nav class="mtk-nav">
            <a href="{{ route('admin.dashboard') }}" class="mtk-nav-item">
                <i class="fal fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.medicines') }}" class="mtk-nav-item">
                <i class="fal fa-box-alt"></i><span>Inventory (Medicines)</span>
            </a>
            <a href="{{ route('admin.users') }}" class="mtk-nav-item active">
                <i class="fal fa-users-crown"></i><span>Admin & User Records</span>
            <a href="{{ route('admin.intakes') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Manage Patient Intakes</span>
            </a>
            </a>
            <a href="{{ route('admin.logs') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Activity Reports</span>
            </a>
        </nav>

        {{-- START: Updated Sidebar Footer Section --}}
        <div class="mtk-sidebar-bottom-actions">
            {{-- Logout Form placed prominently at the bottom --}}
            <form method="POST" action="{{ route('logout') }}" class="w-100 mb-2">
                @csrf
                {{-- Using mtk-btn-secondary for consistent theme styling, but full width (w-100) --}}
                <button type="submit" class="mtk-btn-secondary w-100">
                    <i class="fal fa-sign-out"></i> Logout
                </button>
            </form>
        </div>
        {{-- END: Updated Sidebar Footer Section --}}
    </aside>


    <main class="mtk-main">
        {{-- 🔝 TOPBAR --}}
        <header class="mtk-topbar">
            <div class="mtk-search">
                <i class="fal fa-search mtk-search-icon"></i>
                <input type="text" placeholder="Search users by name or email..." aria-label="Search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    {{-- User initials avatar, using 'UR' as a placeholder for 'User Records' page or replace with dynamic initials --}}
                    <div class="mtk-avatar">UR</div>
                    <div class="mtk-user-meta">
                        <div class="mtk-user-name">{{ Auth::user()->first_name ?? 'Admin' }}</div>
                    </div>
                </div>
                @endauth
            </div>
        </header>
        {{-- END TOPBAR --}}

        <section class="mtk-content">
            <div class="mtk-hero">
                <h2>User Management <i class="fal fa-user-friends"></i></h2>
                <p class="muted">Manage all system users, including administrative and patient records.</p>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
                <div class="alert alert-success mtk-alert"><i class="fal fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <div class="panel">
                <div class="panel-head">
                    <h5 class="panel-title">System User Records</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="UserTable" class="table table-striped table-hover align-middle datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Fullname</th>
                                    <th>Age</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop through the $users collection passed from the controller --}}
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->user_id ?? $user->id }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->full_name }}</td>
                                        <td>{{ $user->age }}</td>
                                        <td>{{ $user->address }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone_number }}</td>
                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-primary mtk-badge-role">Admin</span>
                                            @else
                                                <span class="badge bg-info mtk-badge-role">User</span>
                                            @endif
                                        </td>
                                        <td class="mtk-table-actions">
                                            {{-- Edit Button (triggers modal) --}}
                                            <button 
                                                type="button" 
                                                class="icon-btn text-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal{{ $user->user_id ?? $user->id }}"
                                                title="Edit User">
                                                <i class="fal fa-pencil-alt"></i>
                                            </button>

                                            {{-- Delete Form --}}
                                            <form action="{{ route('admin.users.delete', $user->user_id ?? $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-btn text-danger" title="Delete User" onclick="return confirm('Are you sure you want to delete this user record?')">
                                                    <i class="fal fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

{{-- ✏️ EDIT MODALS (Modal for each user record) --}}
@foreach($users as $user)
    <div class="modal fade" id="editUserModal{{ $user->user_id ?? $user->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $user->user_id ?? $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content mtk-modal-content">
                <form action="{{ route('admin.users.update', $user->user_id ?? $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header mtk-modal-header">
                        <h5 class="modal-title" id="editUserLabel{{ $user->user_id ?? $user->id }}"><i class="fal fa-user-edit"></i> Edit User: {{ $user->username }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">First name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle name</label>
                                <input type="text" name="middle_name" class="form-control" value="{{ $user->middle_name }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Last name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" value="{{ $user->age }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ $user->address }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone_number" class="form-control" value="{{ $user->phone_number }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User (Patient)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer mtk-modal-footer">
                        <button type="button" class="mtk-btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="mtk-btn"><i class="fal fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('scripts')
{{-- Placeholder for DataTables or other script initialization --}}
@endsection