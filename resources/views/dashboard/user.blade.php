@extends('layouts.app')
@section('content')

<div class="container-fluid py-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    <div class="container">
        <div class="row g-4">

            <!-- LEFT SIDEBAR -->
            <div class="col-md-3">
                <!-- Enhanced card with gradient background and modern styling -->
                <div class="card shadow-lg border-0 rounded-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
                    <div class="card-body text-white p-4">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-white bg-opacity-25 text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width: 90px; height: 90px; font-size: 38px; backdrop-filter: blur(10px);">
                                {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                            </div>
                            <h5 class="fw-bold fs-5">{{ auth()->user()->full_name }}</h5>
                            <small class="text-white-50">Patient Profile</small>
                        </div>

                        <div class="mb-3">
                            <small class="text-white-50 d-block">Email</small>
                            <p class="mb-0 fw-semibold small">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="mb-3">
                            <small class="text-white-50 d-block">Phone</small>
                            <p class="mb-0 fw-semibold small">{{ auth()->user()->phone_number }}</p>
                        </div>

                        <div class="mb-3">
                            <small class="text-white-50 d-block">Age</small>
                            <p class="mb-0 fw-semibold small">{{ auth()->user()->age }}</p>
                        </div>

                        <div class="mb-0">
                            <small class="text-white-50 d-block">Address</small>
                            <p class="mb-0 fw-semibold small">{{ auth()->user()->address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-9">
                <!-- Modern card with refined header and better spacing -->
                <div class="card shadow-lg border-0 rounded-4" style="overflow: hidden;">

                    <div class="card-header border-0 pt-4 pb-3 px-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold">
                                <i class="fas fa-pills me-2"></i>Medications to Intake
                            </h4>

                            @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm rounded-pill px-4 fw-semibold">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                            @endauth
                        </div>
                    </div>

                    <div class="card-body p-4">

                        @if(session('success'))
                            <!-- Enhanced alert with gradient background -->
                            <div class="alert alert-success rounded-3 border-0 mb-4" style="background: linear-gradient(135deg, #d4fc79 0%, #96f756 100%); color: #2d5016;">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <!-- Styled table with modern colors and hover effects -->
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); border-bottom: 2px solid #667eea;">
                                        <th class="fw-bold text-dark">Medicine</th>
                                        <th class="fw-bold text-dark">Quantity</th>
                                        <th class="fw-bold text-dark">Interval (min)</th>
                                        <th class="fw-bold text-dark">Intake Time</th>
                                        <th class="fw-bold text-dark">Status</th>
                                        <th class="fw-bold text-dark text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($intakes as $intake)
                                        <tr style="transition: all 0.3s ease;">
                                            <td class="fw-semibold">{{ $intake->medicine->medicine_name ?? '—' }}</td>
                                            <td>{{ $intake->quantity ?? '1' }}</td>
                                            <td>{{ $intake->medicine->intake_interval_minutes ?? '—' }}</td>
                                            <td>{{ $intake->intake_time->format('Y-m-d H:i') }}</td>

                                            <td>
                                                @if($intake->status)
                                                    <!-- Gradient badge for taken status -->
                                                    <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: #1a4d3e;">
                                                        <i class="fas fa-check-circle me-1"></i>Taken
                                                    </span>
                                                @else
                                                    <!-- Gradient badge for pending status -->
                                                    <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: #5a2e2e;">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if(!$intake->status)
                                                    <div class="d-flex justify-content-center gap-2">

                                                        <!-- Confirm Intake -->
                                                        <form method="POST" action="{{ route('user.intake.confirm', $intake->id) }}" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-sm rounded-pill px-3 fw-semibold text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                                <i class="fas fa-check me-1"></i>Confirm
                                                            </button>
                                                        </form>

                                                        <!-- Return -->
                                                        <button class="btn btn-sm rounded-pill px-3 fw-semibold text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#returnModal{{ $intake->id }}">
                                                            <i class="fas fa-undo me-1"></i>Return
                                                        </button>
                                                    </div>
                                                @else
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>Confirmed {{ $intake->confirmed_at ? $intake->confirmed_at->diffForHumans() : '' }}
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Return Modal -->
                                        <!-- Enhanced modal with gradient header -->
                                        <div class="modal fade" id="returnModal{{ $intake->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 shadow-lg border-0">

                                                    <form method="POST" action="{{ route('user.intake.return', $intake->id) }}">
                                                        @csrf

                                                        <div class="modal-header rounded-top-4 text-white border-0 py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                            <h5 class="modal-title fw-bold">
                                                                <i class="fas fa-undo me-2"></i>Return: {{ $intake->medicine->medicine_name ?? 'Medicine' }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body p-4">
                                                            <p class="mb-3"><strong>Amount: </strong>{{ $intake->quantity ?? 1 }}</p>

                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Quantity to Return</label>
                                                                <input type="number" name="quantity" class="form-control rounded-3 border-2"
                                                                       min="1" max="{{ $intake->quantity ?? 1 }}"
                                                                       value="1" required style="border-color: #667eea;">
                                                            </div>

                                                            <div class="mb-0">
                                                                <label class="form-label fw-semibold">Remarks</label>
                                                                <textarea name="remarks" class="form-control rounded-3 border-2" rows="3" style="border-color: #667eea;"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn text-white rounded-pill px-4 fw-semibold" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
                                                                <i class="fas fa-check me-1"></i>Submit Return
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                                                    <p class="mt-2 fw-semibold">No scheduled intakes found.</p>
                                                </div>
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
