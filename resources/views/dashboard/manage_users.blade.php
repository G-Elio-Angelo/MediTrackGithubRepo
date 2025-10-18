@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h2 class="mb-4 text-center">Manage Users</h2>

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
          <td>
            <form action="{{ route('admin.users.delete', $user->user_id ?? $user->id) }}" method="POST">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
