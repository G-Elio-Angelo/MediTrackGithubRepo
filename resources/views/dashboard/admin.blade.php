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
  </div>

  {{-- ===== CHARTS SECTION ===== --}}
  <div class="row g-4">
    {{-- === USERS CHART === --}}
    <div class="col-md-12">
      <div class="card shadow-sm p-4 border-0 rounded-4">
        <h5 class="text-center mb-3 fw-semibold">User Distribution</h5>
        <canvas id="userChart" height="100"></canvas>
        <h6 class="mt-4 fw-bold">List of Users:</h6>
        <ul>
          @foreach($users as $u)
            <li>{{ "Username - " . $u->username }} , {{"Email - ". $u->email }} , {{"Phone Number - ". $u->phone_number }}</li>
          @endforeach
        </ul>
      </div>
    </div>

    {{-- === MEDICINES CHART === --}}
    <div class="col-md-12">
      <div class="card shadow-sm p-4 border-0 rounded-4">
        <h5 class="text-center mb-3 fw-semibold">Medicine Stocks Overview</h5>
        <canvas id="medicineChart" height="100"></canvas>
        <h6 class="mt-4 fw-bold">List of Medicines:</h6>
        <table class="table table-sm table-striped align-middle">
          <thead class="table-light">
            <tr><th>Name</th><th>Stock</th></tr>
          </thead>
          <tbody>
            @foreach($medicines as $m)
              <tr>
                <td>{{ $m->medicine_name }}</td>
                <td>{{ $m->stock }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- === LOW STOCK CHART === --}}
<div class="col-md-12">
  <div class="card shadow-sm p-4 border-0 rounded-4">
    <h5 class="text-center mb-3 fw-semibold text-danger">Low Stock Alerts</h5>
    <canvas id="lowStockChart" height="100"></canvas>

    <h6 class="mt-4 fw-bold">Medicines Running Low:</h6>

    @if($lowStock->isNotEmpty())
      <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-danger">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Medicine Name</th>
            <th scope="col">Batch Number</th>
            <th scope="col">Stock Left</th>
            <th scope="col">Expiry Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($lowStock as $index => $l)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $l->medicine_name }}</td>
              <td>{{ $l->batch_number ?? 'N/A' }}</td>
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
        'lowStockValues' => $lowStockValues
    ];
?>
<script>
    window.dashboardData = <?php echo json_encode($dashboardData); ?>;
</script>

<!-- Custom Dashboard JS -->
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
