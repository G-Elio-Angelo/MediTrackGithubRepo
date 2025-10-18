@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h2 class="mb-4 text-center">Manage Medicine Stocks</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Stock</th>
        <th>Expiry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($medicines as $med)
        <tr>
          <td>{{ $med->id }}</td>
          <td>{{ $med->name }}</td>
          <td class="{{ $med->stock < 10 ? 'text-danger' : '' }}">{{ $med->stock }}</td>
          <td>{{ $med->expiry_date }}</td>
          <td>
            <form action="{{ route('admin.medicines.delete', $med->id) }}" method="POST">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this medicine?')">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
