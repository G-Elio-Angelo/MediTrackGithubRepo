@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Edit Medicine Intake</h2>
    <a href="{{ route('admin.intakes') }}" class="btn btn-secondary">← Back to Intakes</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.intakes.update', $intake->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">User</label>
      <select name="user_id" class="form-select" required>
        @foreach($users as $u)
          <option value="{{ $u->user_id }}" {{ $u->user_id == $intake->user_id ? 'selected' : '' }}>{{ $u->full_name ?? $u->username }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Medicine</label>
      <select name="medicine_id" class="form-select" required>
        @foreach($medicines as $m)
          <option value="{{ $m->id }}" {{ $m->id == $intake->medicine_id ? 'selected' : '' }}>{{ $m->medicine_name }} (Batch: {{ $m->batch_number ?? 'N/A' }})</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Intake Time</label>
      <input type="datetime-local" name="intake_time" class="form-control" value="{{ $intake->intake_time ? $intake->intake_time->format('Y-m-d\TH:i') : '' }}" required>
    </div>

     <div class="mb-3">
      <label class="form-label">Intake Interval</label>
      <input type="number" name="intake_interval_minutes" class="form-control" value="{{ $intake->medicine->intake_interval_minutes ?? '--' }}" placeholder="Enter intake interval in minutes">
    </div>

    <div class="mb-3 form-check">
      <input type="checkbox" name="status" value="1" class="form-check-input" id="statusCheck" {{ $intake->status ? 'checked' : '' }}>
      <label class="form-check-label" for="statusCheck">Mark as taken</label>
    </div>

   

    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>
@endsection
