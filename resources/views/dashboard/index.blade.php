@extends('layouts.app')

@section('content')
<div class="container py-5">
  <h2 class="mb-4 text-center">MediTrack Dashboard</h2>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <h5>Total Medicines</h5>
        <h3 class="text-primary">{{ $total }}</h3>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <h5>Low Stock (below 10)</h5>
        <h3 class="text-danger">{{ $low }}</h3>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <h5>Expiring Soon (30 days)</h5>
        <h3 class="text-warning">{{ $expiring }}</h3>
      </div>
    </div>
  </div>

  <div class="mt-5 text-center">
    <a href="{{ route('medicines.index') }}" class="btn btn-primary">Manage Medicines</a>
  </div>
</div>
@endsection
