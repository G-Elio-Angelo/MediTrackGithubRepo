@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h2 class="mb-4 text-center">Manage Users</h2>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
</div>


  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
        <tr>
          <td>{{ $user->user_id ?? $user->id }}</td>
          <td>{{ $user->username }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->phone_number }}</td>
          <td>{{ ucfirst($user->role) }}</td>
          <td>
            <!-- Edit Button -->
            <button 
              type="button" 
              class="btn btn-sm btn-primary" 
              data-bs-toggle="modal" 
              data-bs-target="#editUserModal{{ $user->user_id ?? $user->id }}">
              Edit
            </button>

            <!-- Delete Form -->
            <form action="{{ route('admin.users.delete', $user->user_id ?? $user->id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                Delete
              </button>
            </form>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editUserModal{{ $user->user_id ?? $user->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $user->user_id ?? $user->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form action="{{ route('admin.users.update', $user->user_id ?? $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title" id="editUserLabel{{ $user->user_id ?? $user->id }}">Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone_number" class="form-control" value="{{ $user->phone_number }}">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                      <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                      <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
