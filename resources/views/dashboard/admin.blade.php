@extends('layouts.app')

@section('content')
<!-- Include unified CSS AFTER Bootstrap -->
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">

<div class="container py-4">

    {{-- ===== HEADER ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Admin Dashboard</h2>
        @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
        @endauth
    </div>

    {{-- ===== SUMMARY CARDS ===== --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card text-center p-4">
                <h5>Total Users</h5>
                <h3 class="text-primary fw-bold">{{ $totalUsers }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center p-4">
                <h5>Total Medicines</h5>
                <h3 class="text-success fw-bold">{{ $totalMedicines }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center p-4">
                <h5>Low Stock Medicines</h5>
                <h3 class="text-danger fw-bold">{{ $lowStockCount }}</h3>
            </div>
        </div>
    </div>

    {{-- ===== MANAGEMENT BUTTONS ===== --}}
    <div class="text-center mb-5">
        <a href="{{ route('admin.users') }}" class="btn btn-primary m-2 glass-btn">👥 Manage Users</a>
        <a href="{{ route('admin.medicines') }}" class="btn btn-success m-2 glass-btn">💊 Manage Medicines</a>
        <a href="{{ route('admin.intakes') }}" class="btn btn-warning m-2 glass-btn">📋 Manage Patient Intakes</a>
    </div>

    {{-- ===== CHARTS & TABLES ===== --}}
    <div class="row g-4">

        {{-- Medicine Stocks Overview --}}
        <div class="col-md-12">
            <div class="glass-card p-4">
                <h5 class="text-center mb-3 fw-semibold">Medicine Stocks Overview</h5>
                <canvas id="medicineChart" style="width:100%; height:300px;"></canvas>

                <h6 class="mt-4 fw-bold">List of Medicines:</h6>
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
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
                            <td>{{ $m->expiry_date ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Near-Expiry Medicines --}}
        <div class="col-md-12">
            <div class="glass-card p-4">
                <h5 class="text-center mb-3 fw-semibold text-warning">Near-Expiry Medicines</h5>
                <canvas id="expiryChart" style="width:100%; height:300px;"></canvas>

                <h6 class="mt-4 fw-bold">Medicines Nearing Expiry:</h6>
                @if(isset($nearExpiry) && $nearExpiry->isNotEmpty())
                    <table class="table table-sm table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Expire Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nearExpiry as $n)
                            <tr>
                                <td>{{ $n->medicine_name }}</td>
                                <td>{{ $n->expiry_date ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-success text-center mt-3">
                        No medicines are expiring within 10 days.
                    </div>
                @endif
            </div>
        </div>

        {{-- Low Stock Medicines --}}
        <div class="col-md-12">
            <div class="glass-card p-4">
                <h5 class="text-center mb-3 fw-semibold text-danger">Low Stock Alerts</h5>
                <canvas id="lowStockChart" style="width:100%; height:300px;"></canvas>

                <h6 class="mt-4 fw-bold">Medicines Running Low:</h6>
                @if($lowStock->isNotEmpty())
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-danger">
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
                                <td>{{ $l->expiry_date ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-success text-center mt-3">
                        ✅ All medicines have sufficient stock.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ===== CHART DATA ===== --}}
<?php
$dashboardData = [
    'userNames' => $userNames,
    'medicineNames' => $medicineNames,
    'medicineStocks' => $medicineStocks,
    'lowStockNames' => $lowStockNames,
    'lowStockValues' => $lowStockValues,
    'userRoleCounts' => [
        'admin' => ($userRoleCounts['admin'] ?? 0),
        'user' => ($userRoleCounts['user'] ?? 0)
    ],
    'nearExpiryNames' => isset($nearExpiry) ? $nearExpiry->pluck('medicine_name')->values() : [],
    'nearExpiryDates' => isset($nearExpiry) ? $nearExpiry->pluck('expiry_date')->values() : []
];
?>
<script>
    window.dashboardData = <?php echo json_encode($dashboardData); ?>;
</script>

{{-- ChartJS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
