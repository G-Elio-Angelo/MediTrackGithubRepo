@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0 fw-bold">Admin Dashboard</h2>
    {{-- Logout button visible on dashboard for convenience --}}
    @auth
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-outline-secondary">Logout</button>
    </form>
    @endauth
  </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm text-center p-3 border-0 rounded-4">
        <h5>Total Users</h5>
        <h3 class="text-primary fw-bold">{{ $totalUsers }}</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3 border-0 rounded-4">
        <h5>Total Medicines</h5>
        <h3 class="text-success fw-bold">{{ $totalMedicines }}</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3 border-0 rounded-4">
        <h5>Low Stock Medicines</h5>
        <h3 class="text-danger fw-bold">{{ $lowStockCount }}</h3>
      </div>
    </div>
  </div>

  {{-- ===== MANAGEMENT BUTTONS ===== --}}
  <div class="text-center mb-5">
    <a href="{{ route('admin.users') }}" class="btn btn-primary m-2 px-4 py-2 rounded-3 shadow-sm">👥 Manage Users</a>
    <a href="{{ route('admin.medicines') }}" class="btn btn-success m-2 px-4 py-2 rounded-3 shadow-sm">💊 Manage Medicines</a>
    <a href="{{ route('admin.intakes') }}" class="btn btn-warning m-2 px-4 py-2 rounded-3 shadow-sm">📋 Manage Patient Intakes</a>
  </div>

  {{-- ===== CHARTS SECTION ===== --}}
  <div class="row g-4">

    {{-- === MEDICINES CHART === --}}
    <div class="col-md-12">
      <div class="card shadow-sm p-4 border-0 rounded-4">
        <h5 class="text-center mb-3 fw-semibold">Medicine Stocks Overview</h5>
        <div style="width: 80%; height: 300px">
        <canvas id="medicineChart" height="100"></canvas>
        </div>
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

      {{-- === NEAR-EXPIRY CHART === --}}
      <div class="col-md-12">
        <div class="card shadow-sm p-4 border-0 rounded-4">
          <h5 class="text-center mb-3 fw-semibold text-warning">Near-Expiry Medicines</h5>
          <div style="width: 80%; height: 300px">
          <canvas id="expiryChart" height="80"></canvas>
          </div>
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
            <div class="alert alert-success text-center mt-3" role="alert">No medicines are expiring within 10 days.</div>
          @endif
        </div>
      </div>

      {{-- === LOW STOCK CHART === --}}
<div class="col-md-12">
  <div class="card shadow-sm p-4 border-0 rounded-4">
    <h5 class="text-center mb-3 fw-semibold text-danger">Low Stock Alerts</h5>
    <div style="width: 80%; height: 300px">
    <canvas id="lowStockChart" height="100"></canvas>
    </div>
    <h6 class="mt-4 fw-bold">Medicines Running Low:</h6>

    @if($lowStock->isNotEmpty())
      <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-danger">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Medicine Name</th>
            <th scope="col">Stock Left</th>
            <th scope="col">Expiry Date</th>
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
      <div class="alert alert-success text-center mt-3" role="alert">
        ✅ All medicines have sufficient stock.
      </div>
    @endif
  </div>
</div>

  </div>
</div>

{{-- ===== CUSTOM STYLES ===== --}}
<style>
  canvas {
    
    background: #fff;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }
  .card h5 {
    font-weight: 600;
  }
  .table-danger th {
  background-color: #f8d7da !important;
  color: #842029;
}

.table td, .table th {
  vertical-align: middle;
}

</style>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dashboard Data -->
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
    'medicineExpiries' => $medicineExpiries
  ,
  'nearExpiryNames' => isset($nearExpiry) ? $nearExpiry->pluck('medicine_name')->values() : [],
  'nearExpiryDates' => isset($nearExpiry) ? $nearExpiry->pluck('expiry_date')->values() : []
  ];
?>
<script>
    window.dashboardData = <?php echo json_encode($dashboardData); ?>;
</script>

<!-- Custom Dashboard JS -->
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
