@extends('layouts.app')
@section('content')
<div class="container py-4">
  <div class="row">
    <!-- Left sidebar (three-line profile) -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Your Details</h5>
          <p class="mb-1"><strong>Name:</strong><br>{{ auth()->user()->full_name }}</p>
          <p class="mb-1"><strong>Email:</strong><br>{{ auth()->user()->email }}</p>
          <p class="mb-0"><strong>Phone:</strong><br>{{ auth()->user()->phone_number }}</p>
          <p class="mb-0"><strong>Age:</strong><br>{{ auth()->user()->age }}</p>
          <p class="mb-0"><strong>Address:</strong><br>{{ auth()->user()->address }}</p>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="col-md-9">
      <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h4 class="card-title mb-0">Medications to intake</h4>
              @auth
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
              </form>
              @endauth
            </div>

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <table class="table">
            <thead>
              <tr>
                  <th>Medicine name</th>
                  <th>Medicine Quantity</th>
                  <th>Interval (min)</th>
                  <th>Intake Time</th>
                  <th>Intake Status</th>
                  <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($intakes as $intake)
                <tr>
                    <td>{{ $intake->medicine->medicine_name ?? '—' }}</td>
                  <td>{{ $intake->quantity ?? '1' }}</td>
                    <td>{{ $intake->medicine->intake_interval_minutes ?? '—' }}</td>
                  <td>{{ $intake->intake_time->format('Y-m-d H:i') }}</td>
                  <td>
                    @if($intake->status)
                      <span class="badge bg-success">Taken</span>
                    @else
                      <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                  </td>
                  <td>
                    @if(!$intake->status)
                      <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('user.intake.confirm', $intake->id) }}">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-primary">Confirm Intake</button>
                        </form>

                        <!-- Return button opens modal -->
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal{{ $intake->id }}">Return</button>
                      </div>
                    @else
                      <small class="text-muted">Confirmed {{ $intake->confirmed_at ? $intake->confirmed_at->diffForHumans() : '' }}</small>
                    @endif
                  </td>
                </tr>
                
                <!-- Return Modal for this intake -->
                <div class="modal fade" id="returnModal{{ $intake->id }}" tabindex="-1" aria-labelledby="returnLabel{{ $intake->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('user.intake.return', $intake->id) }}">
                        @csrf
                        <div class="modal-header">
                          <h5 class="modal-title" id="returnLabel{{ $intake->id }}">Return {{ $intake->medicine->medicine_name ?? 'Medicine' }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p>Amount <strong>{{ $intake->quantity ?? 1 }}</strong></p>
                          <div class="mb-3">
                            <label class="form-label">Quantity to return</label>
                            <input type="number" name="quantity" class="form-control" min="1" max="{{ $intake->quantity ?? 1 }}" value="1" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-warning">Return</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @empty
                <tr>
                  <td colspan="4">No scheduled intakes.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
