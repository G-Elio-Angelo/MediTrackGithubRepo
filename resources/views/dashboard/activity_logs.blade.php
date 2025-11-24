@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Activity Logs</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
  </div>

  <div class="card p-3 shadow-sm">
    <table id="activityLogsTable" class="table table-striped table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Action</th>
          <th>Meta</th>
          <th>IP</th>
          <th>When</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
  $(function() {
    $('#activityLogsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('admin.logs.data') }}",
        pageLength: 10,
        columns: [
          { data: 'id' },
          { data: 'user' },
          { data: 'action' },
          { data: 'meta' },
          { data: 'ip_address' },
          { data: 'created_at' }
        ],
        order: [[5, 'desc']],
        responsive: true
    });
  });
</script>
@endsection
