@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Patient Medicine Intakes</h2>
    <div>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary me-2">← Back to Dashboard</a>
      <a href="{{ route('admin.intakes') }}" class="btn btn-primary">Refresh</a>
      <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addIntakeModal">+ Add Intake</button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Medicine</th>
        <th>Quantity</th>
        <th>Intake Time</th>
        <th>Intake Interval</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($intakes as $intake)
        <tr>
          <td>{{ $intake->id }}</td>
          <td>{{ $intake->user->full_name ?? $intake->user->username }}</td>
          <td>{{ $intake->medicine->medicine_name ?? '—' }}</td>
            <td>{{ $intake->quantity ?? '1' }}</td>
          <td>{{ $intake->intake_time ? $intake->intake_time->format('Y-m-d H:i') : '—' }}</td>
          <td>{{ $intake->medicine->intake_interval_minutes ?? '—' }} min</td>
          <td>@if($intake->status) <span class="badge bg-success">Taken</span> @else <span class="badge bg-warning text-dark">Pending</span> @endif</td>
          <td>
            <a href="{{ route('admin.intakes.edit', $intake->id) }}" class="btn btn-sm btn-primary">Edit</a>
            <form action="{{ route('admin.intakes.delete', $intake->id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this intake?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- Add Intake Modal -->
<div class="modal fade" id="addIntakeModal" tabindex="-1" aria-labelledby="addIntakeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('admin.intake.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addIntakeLabel">Schedule Medicine Intake for Patient</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select User (Patient)</label>
            <select name="user_id" class="form-select" required>
              <option value="">-- Choose user --</option>
              @foreach($nonAdminUsers as $u)
                <option value="{{ $u->user_id }}">{{ $u->full_name ?? $u->username }} &mdash; {{ $u->email }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Medicine</label>
            <select name="medicine_id" class="form-select" required>
              <option value="">-- Choose medicine  --</option>
              @foreach($intakeMedicines as $m)
                <option value="{{ $m->id }}">{{ $m->medicine_name }} (Batch: {{ $m->batch_number ?? 'N/A' }}, Stocks Left: {{ $m->stock }}, Exp: {{ $m->expiry_date }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity to give</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Interval (minutes)</label>
            <input type="number" name="interval_minutes" class="form-control" min="1" value="30" required>
            <div class="form-text">Optional: set interval to round intake time for this schedule.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Intake Time</label>
            <input type="datetime-local" name="intake_time" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Schedule Intake</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Interval is now set per-intake inside the Add Intake modal -->
@endsection
