@extends('layouts.app')

@section('content')
<div class="container py-5">
    {{-- Main Header --}}
    <h1 class="display-4 fw-bolder text-center mb-2" style="color: #1F2937;">MediTrack Overview</h1>
    <p class="lead text-center text-muted mb-5">Current health and inventory statistics at a glance.</p>

    {{-- ===== KEY METRICS CARDS (Enhanced Design) ===== --}}
    <div class="row g-4 justify-content-center">

        {{-- Total Medicines Card --}}
        <div class="col-sm-6 col-md-4">
            <div class="card p-5 h-100 shadow-xl rounded-2xl border-0 transform hover:scale-105 transition duration-300" 
                 style="background: linear-gradient(135deg, #E0F7FA 0%, #B2EBF2 100%);">
                <div class="text-center">
                    <i class="fas fa-pills fa-3x mb-3" style="color: #00BCD4;"></i>
                    <h5 class="text-secondary fw-semibold mb-2">Total Medicine Types</h5>
                    <h2 class="display-4 fw-bolder" style="color: #0097A7;">{{ $total }}</h2>
                </div>
            </div>
        </div>

        {{-- Low Stock Card --}}
        <div class="col-sm-6 col-md-4">
            <div class="card p-5 h-100 shadow-xl rounded-2xl border-0 transform hover:scale-105 transition duration-300"
                 style="background: linear-gradient(135deg, #FFEBEA 0%, #FFCDD2 100%);">
                <div class="text-center">
                    <i class="fas fa-exclamation-circle fa-3x mb-3" style="color: #E53935;"></i>
                    <h5 class="text-secondary fw-semibold mb-2">Low Stock Alert (≤ 10)</h5>
                    <h2 class="display-4 fw-bolder" style="color: #C62828;">{{ $low }}</h2>
                </div>
            </div>
        </div>

        {{-- Expiring Soon Card --}}
        <div class="col-sm-6 col-md-4">
            <div class="card p-5 h-100 shadow-xl rounded-2xl border-0 transform hover:scale-105 transition duration-300"
                 style="background: linear-gradient(135deg, #FFFDE7 0%, #FFF9C4 100%);">
                <div class="text-center">
                    <i class="fas fa-clock fa-3x mb-3" style="color: #FFB300;"></i>
                    <h5 class="text-secondary fw-semibold mb-2">Expiring Soon (30 days)</h5>
                    <h2 class="display-4 fw-bolder" style="color: #FFA000;">{{ $expiring }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ACTION BUTTON ===== --}}
    <div class="mt-6 pt-5 text-center">
        <a href="{{ route('medicines.index') }}" 
           class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg transform hover:scale-105 transition duration-300"
           style="background-color: #1A73E8; border-color: #1A73E8;">
            <i class="fas fa-list-alt me-2"></i> Go to Inventory Management
        </a>
    </div>

</div>
@endsection