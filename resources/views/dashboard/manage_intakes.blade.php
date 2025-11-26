@extends('layouts.app')

@section('content')
{{-- Include CSS and Font Awesome for consistent styling --}}
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
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
            <a href="{{ route('admin.users') }}" class="mtk-nav-item">
                <i class="fal fa-users-crown"></i><span>Admin & User Records</span>
            <a href="{{ route('admin.intakes') }}" class="mtk-nav-item active">
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
        {{-- TOPBAR --}}
        <header class="mtk-topbar">
            <div class="mtk-search">
                <i class="fal fa-search mtk-search-icon"></i>
                <input type="text" placeholder="Search intakes..." aria-label="Search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    <div class="mtk-avatar">MI</div>
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
                <h2>Patient Medicine Intakes <i class="fal fa-calendar-alt"></i></h2>
                <p class="muted">Monitor and schedule patient medicine consumption records.</p>
            </div>

            {{-- ALERTS & MESSAGES --}}
            @if(session('success'))
                <div class="alert alert-success mtk-alert"><i class="fal fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mtk-alert"><i class="fal fa-times-circle"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mtk-alert">
                    <h6 class="mb-1"><i class="fal fa-exclamation-triangle"></i> Validation Errors:</h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="panel">
                <div class="panel-head">
                    <h5 class="panel-title">Intake History & Schedule</h5>
                    <div class="mtk-actions m-0 p-0">
                        <a href="{{ route('admin.dashboard') }}" class="mtk-btn-secondary"><i class="fal fa-arrow-left"></i> Dashboard</a>
                        <a href="{{ route('admin.intakes') }}" class="mtk-btn-secondary"><i class="fal fa-sync"></i> Refresh</a>
                        <button class="mtk-btn" data-bs-toggle="modal" data-bs-target="#addIntakeModal">
                            <i class="fal fa-plus-circle"></i> Schedule Intake
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        {{-- Used the defined table classes --}}
                        <table class="table table-striped table-hover align-middle datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Medicine</th>
                                    <th>Qty</th>
                                    <th>Intake Time</th>
                                    <th>Interval</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($intakes as $intake)
                                    <tr>
                                        <td>{{ $intake->id }}</td>
                                        <td>{{ $intake->user->full_name ?? $intake->user->username }}</td>
                                        <td>{{ $intake->medicine->medicine_name ?? '—' }}</td>
                                        <td class="text-center">{{ $intake->quantity ?? '1' }}</td>
                                        <td>{{ $intake->intake_time ? $intake->intake_time->format('M d, Y H:i') : '—' }}</td>
                                        <td>{{ $intake->medicine->intake_interval_minutes ?? '—' }} min</td>
                                        <td>
                                            @if($intake->status)
                                                <span class="badge bg-success mtk-badge-taken"><i class="fal fa-check"></i> Taken</span>
                                            @else
                                                <span class="badge bg-warning text-dark mtk-badge-pending"><i class="fal fa-clock"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td class="mtk-table-actions">
                                            <a href="{{ route('admin.intakes.edit', $intake->id) }}" class="icon-btn text-primary me-2" title="Edit"><i class="fal fa-pencil-alt"></i></a>
                                            <form action="{{ route('admin.intakes.delete', $intake->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Replaced the JS confirm() with a custom message since confirm() is often blocked/undesirable --}}
                                                <button type="submit" class="icon-btn text-danger" title="Delete" onsubmit="return mtkConfirmDelete(event)"><i class="fal fa-trash-alt"></i></button>
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

<div class="modal fade" id="addIntakeModal" tabindex="-1" aria-labelledby="addIntakeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mtk-modal-content">
            <form action="{{ route('admin.intake.store') }}" method="POST">
                @csrf
                <div class="modal-header mtk-modal-header">
                    <h5 class="modal-title" id="addIntakeLabel"><i class="fal fa-calendar-plus"></i> Schedule New Intake</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select User (Patient)</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Choose user --</option>
                            @foreach($nonAdminUsers as $u)
                                <option value="{{ $u->user_id }}">{{ $u->full_name ?? $u->username }} &mdash; {{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Medicine</label>
                        <select name="medicine_id" class="form-select" required>
                            <option value="">-- Choose medicine --</option>
                            @foreach($intakeMedicines as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->medicine_name }} 
                                    (Batch: {{ $m->batch_number ?? 'N/A' }}, 
                                    Stocks Left: <span class="{{ $m->stock < 10 ? 'text-danger' : '' }}">{{ $m->stock }}</span>, 
                                    Exp: {{ $m->expiry_date }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to give</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Intake Time</label>
                        <input type="datetime-local" name="intake_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interval (minutes)</label>
                        <input type="number" name="interval_minutes" class="form-control" min="1" value="30" required>
                        <div class="form-text">Sets the medicine's dosage interval for automated future scheduling.</div>
                    </div>
                </div>
                <div class="modal-footer mtk-modal-footer">
                    <button type="button" class="mtk-btn-secondary" data-bs-dismiss="modal"><i class="fal fa-times"></i> Close</button>
                    <button type="submit" class="mtk-btn"><i class="fal fa-clock"></i> Schedule Intake</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Custom Modal for Deletion Confirmation (Replacing alert/confirm) --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content mtk-modal-content">
            <div class="modal-header mtk-modal-header border-bottom-0">
                <h5 class="modal-title text-danger" id="deleteConfirmLabel"><i class="fal fa-exclamation-triangle"></i> Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete this intake record?</p>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="mtk-btn-secondary me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="mtk-btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- Ensure you include DataTables initialization if using the 'datatable' class --}}
<script>
    $(document).ready(function() {
        if ($('.datatable').length) {
            $('.datatable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        }
    });

    function mtkConfirmDelete(event) {
        event.preventDefault();
            const form = event.target.closest('form');
                $('#deleteForm').attr('action', form.action);
                const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteConfirmModal.show();
                return false;
    }
</script>
@endsection