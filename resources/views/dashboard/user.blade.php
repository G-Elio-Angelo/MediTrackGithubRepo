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
                <th>Intake Time</th>
                <th>Intake Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($intakes as $intake)
                <tr>
                  <td>{{ $intake->medicine->medicine_name ?? '—' }}</td>
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
                      <form method="POST" action="{{ route('user.intake.confirm', $intake->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Confirm Intake</button>
                      </form>
                    @else
                      <small class="text-muted">Confirmed {{ $intake->confirmed_at ? $intake->confirmed_at->diffForHumans() : '' }}</small>
                    @endif
                  </td>
                </tr>
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
