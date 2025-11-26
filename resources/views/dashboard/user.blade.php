@extends('layouts.app')

@section('content')

{{-- Custom CSS for subtle colors and font consistency (assuming a clean, modern font) --}}
<style>
    .bg-light-blue { background-color: #f5f8fa; }
    .text-primary-dark { color: #0056b3; }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }
    .badge-status {
        font-weight: 600;
        padding: .4em .7em;
        font-size: 0.75rem;
    }
</style>

{{-- Main Wrapper with a clean, light background for contrast --}}
<div class="container-fluid py-5 bg-light-blue" style="min-height: 100vh;">
    <div class="container">
        
        {{-- Page Header: Enhanced contrast and spacing --}}
        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h1 class="fw-bolder text-dark mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>My Medication Schedule</h1>
                <p class="text-secondary mb-0 lead-sm">Track and manage your daily intakes.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-primary border border-primary-subtle shadow-sm p-3 rounded-4 fw-bold">
                    <i class="fas fa-calendar-day me-2"></i> {{ date('l, F d, Y') }}
                </span>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-3 col-md-4">
                <div class="card shadow-lg border-0 rounded-4 bg-white h-100 card-hover">
                    <div class="card-body text-center p-4">
                        
                        {{-- Avatar: Larger, more prominent, with subtle inner shadow --}}
                        <div class="mb-4 position-relative d-inline-block">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 110px; height: 110px; font-size: 40px; border: 4px solid #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1), inset 0 0 5px rgba(0,0,0,0.05);">
                                {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                            </div>
                            <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle">
                                <span class="visually-hidden">Active</span>
                            </span>
                        </div>

                        <h4 class="fw-bold text-dark mb-1">{{ auth()->user()->full_name }}</h4>
                        <div class="text-start p-3 rounded-4 mb-4" style="background-color: #f8f9fa;">
                            
                            {{-- Contact Info Boxes --}}
                            <div class="mb-3">
                                <span class="text-secondary small fw-medium d-block mb-1"><i class="fas fa-envelope me-2"></i>Email</span>
                                <p class="fw-semibold text-dark small mb-0 text-break">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="mb-3">
                                <span class="text-secondary small fw-medium d-block mb-1"><i class="fas fa-phone me-2"></i>Phone</span>
                                <p class="fw-semibold text-dark small mb-0">{{ auth()->user()->phone_number }}</p>
                            </div>

                            <div class="mb-0">
                                <span class="text-secondary small fw-medium d-block mb-1"><i class="fas fa-birthday-cake me-2"></i>Age</span>
                                <p class="fw-semibold text-dark small mb-0">{{ auth()->user()->age }} years old</p>
                            </div>
                        </div>
                        
                        {{-- LOGOUT BUTTON --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-pill shadow-sm py-2 fw-medium">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                        
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="card shadow-lg border-0 rounded-4 bg-white h-100">
                    
                    <div class="card-header bg-white border-bottom p-4 rounded-top-4">
                        <h4 class="fw-bold m-0 text-primary-dark"><i class="fas fa-list-ul me-2"></i>Today's Intakes</h4>
                        <p class="text-muted small m-0">Review and confirm your medication schedule below.</p>
                    </div>

                    <div class="card-body p-4">

                        {{-- Success Alert --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4 fw-medium" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-primary bg-opacity-10" style="--bs-table-bg-type: none;">
                                    <tr class="text-primary">
                                        <th class="py-3 ps-3 rounded-start text-uppercase small">Medicine Name</th>
                                        <th class="py-3 text-uppercase small">Qty</th>
                                        <th class="py-3 text-uppercase small">Interval</th>
                                        <th class="py-3 text-uppercase small">Schedule</th>
                                        <th class="py-3 text-uppercase small">Status</th>
                                        <th class="py-3 pe-3 text-center text-uppercase small rounded-end">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($intakes as $intake)
                                        <tr class="border-bottom">
                                            <td class="ps-3 fw-semibold text-dark">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-capsules fa-lg"></i>
                                                    </div>
                                                    <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                                        {{ $intake->medicine->medicine_name ?? 'Unknown Medicine' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border fw-medium">{{ $intake->quantity ?? '1' }}</span></td>
                                            <td class="text-secondary small">{{ $intake->medicine->intake_interval_minutes ?? '—' }} mins</td>
                                            <td class="fw-bold text-dark">{{ $intake->intake_time->format('h:i A') }}</td>

                                            <td>
                                                @if($intake->status)
                                                    <span class="badge bg-success badge-status text-white rounded-pill px-3">
                                                        <i class="fas fa-check me-1"></i> Taken
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning badge-status text-dark rounded-pill px-3">
                                                        <i class="fas fa-hourglass-half me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-center pe-3">
                                                @if(!$intake->status)
                                                    <div class="d-flex justify-content-center gap-2">
                                                        {{-- Confirm Button --}}
                                                        <form method="POST" action="{{ route('user.intake.confirm', $intake->id) }}">
                                                            @csrf
                                                            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-medium">
                                                                Confirm Intake
                                                            </button>
                                                        </form>

                                                        {{-- Return Button (using a lighter outline style) --}}
                                                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#returnModal{{ $intake->id }}">
                                                            Return
                                                        </button>
                                                    </div>
                                                @else
                                                    <small class="text-success fst-italic fw-medium">
                                                        Confirmed {{ $intake->confirmed_at ? $intake->confirmed_at->format('h:i A') : '' }}
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Modal: Kept Logic, Updated UI for modern look --}}
                                        <div class="modal fade" id="returnModal{{ $intake->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4">
                                                    <form method="POST" action="{{ route('user.intake.return', $intake->id) }}">
                                                        @csrf
                                                        <div class="modal-header bg-danger text-white border-0 rounded-top-4 py-3">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-undo-alt me-2"></i>Return Medicine</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="alert alert-danger-subtle text-danger border border-danger-subtle rounded-3 small p-3 mb-4 fw-medium">
                                                                <i class="fas fa-exclamation-circle me-1"></i> Warning: This action updates the inventory and patient record.
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-secondary small text-uppercase fw-bold">Medicine</label>
                                                                <input type="text" class="form-control form-control-lg bg-light border-0" value="{{ $intake->medicine->medicine_name ?? 'Medicine' }}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-secondary small text-uppercase fw-bold">Quantity to Return</label>
                                                                <input type="number" name="quantity" class="form-control form-control-lg" min="1" max="{{ $intake->quantity ?? 1 }}" value="1" required>
                                                                <div class="form-text">Max returnable: <span class="fw-bold">{{ $intake->quantity ?? 1 }}</span></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-secondary small text-uppercase fw-bold">Reason / Remarks</label>
                                                                <textarea name="remarks" class="form-control" rows="3" placeholder="Why are you returning this?" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-medium">Submit Return</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted bg-light rounded-bottom-4">
                                                <div class="mb-3">
                                                    <i class="fas fa-clipboard-check fa-4x text-light"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark-emphasis">All caught up!</h5>
                                                <p class="small mb-0">No scheduled intakes found for today. Enjoy your day!</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection