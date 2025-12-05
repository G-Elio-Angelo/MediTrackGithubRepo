@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" xintegrity="sha384-AYmEC3Yw5U5dD7i7t53X30m46O7hF6+I23g9M+B4hL2t8F9gYt5Tz30O+gR7/d6KxYj" crossorigin="anonymous">


<div class="mtk-app">

    {{-- SIDEBAR --}}
    <aside class="mtk-sidebar">
        <div class="mtk-brand">
           <h1 class="mtk-brand-title">MediTrack</h1>
        </div>

        <nav class="mtk-nav">
            <a href="#" class="mtk-nav-item active">
                <i class="fal fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.medicines') }}" class="mtk-nav-item">
                <i class="fal fa-box-alt"></i><span>Inventory (Medicines)</span>
            </a>
            <a href="{{ route('admin.users') }}" class="mtk-nav-item">
                <i class="fal fa-users-crown"></i><span>Admin & User Records</span>
            <a href="{{ route('admin.intakes') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Manage Patient Intakes</span>
            </a>
            </a>
            <a href="{{ route('admin.logs') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Activity Reports</span>
            </a>
        </nav>

        <div class="mtk-sidebar-bottom-actions">
            <form method="POST" action="{{ route('logout') }}" class="w-100 mb-2">
                @csrf
                <button type="submit" class="mtk-btn-secondary w-100">
                    <i class="fal fa-sign-out"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="mtk-main">
        <header class="mtk-topbar">
            <div class="mtk-search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    <div class="mtk-avatar">HW</div>
                    <div class="mtk-user-meta">
                        <div class="mtk-user-name">{{ Auth::user()->first_name ?? 'Admin' }}</div>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <section class="mtk-content">
            <div class="mtk-hero">
                <h2>Welcome, Admin <i class="fal fa-user-crown"></i></h2>
                <p class="muted">Today is {{ date('F d, Y') }} — Overview of inventory and alerts.</p>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="mtk-grid">
                <div class="mtk-card">
                    <i class="fal fa-users mtk-card-icon"></i>
                    <div class="mtk-card-meta">Total Users</div>
                    <div class="mtk-card-value">{{ $totalUsers }}</div>
                </div>

                <div class="mtk-card">
                    <i class="fal fa-box-open mtk-card-icon text-warning"></i>
                    <div class="mtk-card-meta">Low Stock Medicines</div>
                    <div class="mtk-card-value text-danger">{{ $lowStockCount }}</div>
                </div>

                <div class="mtk-card">
                    <i class="fal fa-pills mtk-card-icon"></i>
                    <div class="mtk-card-meta">Total Medicine Types</div>
                    <div class="mtk-card-value">{{ $totalMedicines }}</div>
                </div>

                <div class="mtk-card">
                    <i class="fal fa-calendar-times mtk-card-icon text-warning"></i>
                    <div class="mtk-card-meta">Medicine Expiring</div>
                    <div class="mtk-card-value">{{ isset($nearExpiry) ? $nearExpiry->count() : 0 }}</div>
                </div>
            </div>
            

            {{-- ALERTS & TABLES --}}
            <div class="mtk-row">
                <div class="mtk-column">
                    <div class="panel">
                        <div class="panel-head">
                            <h5 class="panel-title">🛑 Low Stock Alerts</h5>
                            <a href="{{ route('admin.medicines', ['status' => 'low']) }}" class="panel-action">View All</a>
                        </div>
                        <div class="panel-body">
                            @if($lowStock->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Medicine Name</th>
                                            <th>Stock Left</th>
                                            <th>Expiry Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lowStock as $index => $l)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $l->medicine_name }}</td>
                                            <td class="text-danger fw-bold">{{ $l->stock }}</td>
                                            <td>{{ $l->expiry_date ? \Carbon\Carbon::parse($l->expiry_date)->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="empty-state">✅ All medicines have sufficient stock.</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="panel">
                        <div class="panel-head">
                            <h5 class="panel-title">📊 Medicine Stocks Overview</h5>
                            <a href="{{ route('admin.medicines') }}" class="panel-action">Full Inventory</a>
                        </div>
                        <div class="panel-body">
                            <div class="chart-wrapper">
                                <canvas id="medicineChart"></canvas>
                            </div>
                            <h6 class="mt-4 panel-subtitle">Full Medicine List</h6>
                            <div class="table-responsive">
                                <table id="MedicineList" class="table table-striped"> {{-- Updated class to table-striped --}}
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Stock</th>
                                            <th>Expiry Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($medicines as $m)
                                        <tr>
                                            <td>{{ $m->medicine_name }}</td>
                                            <td>{{ $m->stock }}</td>
                                            <td>{{ $m->expiry_date ? \Carbon\Carbon::parse($m->expiry_date)->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mtk-column mtk-column-small">
                    <div class="panel">
                        <div class="panel-head">
                            <h5 class="panel-title">⚠️ Near-Expiry Medicines</h5>
                            <a href="{{ route('admin.medicines', ['status' => 'expiring']) }}" class="panel-action">View All</a>
                        </div>
                        <div class="panel-body">
                            @if(isset($nearExpiry) && $nearExpiry->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Expire Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nearExpiry as $n)
                                        <tr>
                                            <td>{{ $n->medicine_name }}</td>
                                            <td>{{ $n->expiry_date ? \Carbon\Carbon::parse($n->expiry_date)->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="empty-state">No medicines are expiring within 10 days.</div>
                            @endif
                        </div>
                    </div>


                </div>
            </div>

        </section>
    </main>
</div>

<?php
$dashboardData = [
    'userNames' => $userNames,
    'medicineNames' => $medicineNames,
    'medicineStocks' => $medicineStocks,
    'lowStockNames' => $lowStockNames,
    'lowStockValues' => $lowStockValues,
    
    'nearExpiryNames' => isset($nearExpiry) ? $nearExpiry->pluck('medicine_name')->values() : [],
    'nearExpiryDates' => isset($nearExpiry) ? $nearExpiry->pluck('expiry_date')->values() : []
];
?>
<script>window.dashboardData = <?php echo json_encode($dashboardData); ?>;</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection