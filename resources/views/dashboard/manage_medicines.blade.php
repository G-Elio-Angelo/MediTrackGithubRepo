@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Medicines</h2>
    <div>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary me-2">← Back to Dashboard</a>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMedicineModal">+ Add Medicine</button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Medicine Name</th>
        <th>Batch Number</th>
        <th>Stock</th>
        <th>Expiry Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($medicines as $medicine)
        <tr>
          <td>{{ $medicine->id }}</td>
          <td>{{ $medicine->medicine_name }}</td>
          <td>{{ $medicine->batch_number }}</td>
          <td>{{ $medicine->stock }}</td>
          <td>{{ $medicine->expiry_date }}</td>
          <td>
            <!-- Edit Button -->
            <button 
              type="button" 
              class="btn btn-sm btn-primary" 
              data-bs-toggle="modal" 
              data-bs-target="#editMedicineModal{{ $medicine->id }}">
              Edit
            </button>

            <!-- Delete Form -->
            <form action="{{ route('admin.medicines.delete', $medicine->id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this medicine?')">
                Delete
              </button>
            </form>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editMedicineModal{{ $medicine->id }}" tabindex="-1" aria-labelledby="editMedicineLabel{{ $medicine->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form action="{{ route('admin.medicines.update', $medicine->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title" id="editMedicineLabel{{ $medicine->id }}">Edit Medicine</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Medicine Name</label>
                    <input type="text" name="medicine_name" class="form-control" value="{{ $medicine->medicine_name }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Batch Number</label>
                    <input type="text" name="batch_number" class="form-control" value="{{ $medicine->batch_number }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="{{ $medicine->stock }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ $medicine->expiry_date }}" required>
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

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-labelledby="addMedicineLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('admin.medicines.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addMedicineLabel">Add New Medicine</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Medicine Name</label>
            <input type="text" name="medicine_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Batch Number</label>
            <input type="text" name="batch_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Medicine</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
