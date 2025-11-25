@extends('layouts.app')

@section('content')
{{-- Include CSS and Font Awesome for consistent styling --}}
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" xintegrity="sha384-AYmEC3Yw5U5dD7i7t53X30m46O7hF6+I23g9M+B4hL2t8F9gYt5Tz30O+gR7/d6KxYj" crossorigin="anonymous">
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<div class="mtk-app">
    {{-- SIDEBAR - Re-include the structure for layout consistency --}}
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
            </a>
            {{-- Set 'Activity Reports' as active --}}
            <a href="{{ route('admin.logs') }}" class="mtk-nav-item active">
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
    {{-- END SIDEBAR --}}

    <main class="mtk-main">
        {{-- TOPBAR - Use the same topbar structure for visual completeness --}}
        <header class="mtk-topbar">
            <div class="mtk-search">
                <i class="fal fa-search mtk-search-icon"></i>
                <input type="text" placeholder="Search logs..." aria-label="Search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    <div class="mtk-avatar">AL</div> {{-- Changed initials to reflect page/user --}}
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
                <h2>Activity Logs <i class="fal fa-clipboard-list-check"></i></h2>
                <p class="muted">Detailed record of user actions within the system.</p>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h5 class="panel-title">System Activity Records</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        {{-- Applied the new table classes: table-striped and table-hover --}}
                        <table id="activityLogsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables will populate this tbody --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

@endsection

@section('scripts')
{{-- Load jQuery and DataTables libraries before the script that uses them --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    $(function() {
        $('#activityLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.logs.data') }}",
            pageLength: 10,
            columns: [
                { data: 'id' },
                { data: 'user' },
                { data: 'action' },
                { data: 'ip_address' },
                { data: 'created_at' }
            ],
            order: [[4, 'desc']],
            responsive: true
        });
    });
</script>
@endsection