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

  <table class="table datatable table-striped table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Medicine Name</th>
        <th>Batch Number</th>
        <th>Supplier</th>
        <th>Stock</th>
        <th>Delivered Date</th>
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
          <td>{{ $medicine->supplier_name ?? '—' }}</td>
          <td>{{ $medicine->stock }}</td>
          <td>{{ $medicine->delivered_date ? \Carbon\Carbon::parse($medicine->delivered_date)->format('Y-m-d') : '—' }}</td>
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

            <!-- Return Button -->
            <button 
              type="button"
              class="btn btn-sm btn-warning ms-1"
              data-bs-toggle="modal"
              data-bs-target="#returnMedicineModal{{ $medicine->id }}">
              Return
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
                    <label class="form-label">Supplier Name</label>
                    <input type="text" name="supplier_name" class="form-control" value="{{ $medicine->supplier_name }}">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="{{ $medicine->stock }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Delivered Date</label>
                    <input type="date" name="delivered_date" class="form-control" value="{{ $medicine->delivered_date ? \Carbon\Carbon::parse($medicine->delivered_date)->format('Y-m-d') : '' }}">
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

<!-- Return Modal(s) -->
@foreach($medicines as $medicine)
  <div class="modal fade" id="returnMedicineModal{{ $medicine->id }}" tabindex="-1" aria-labelledby="returnMedicineLabel{{ $medicine->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="{{ route('admin.medicines.return', $medicine->id) }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="returnMedicineLabel{{ $medicine->id }}">Record Return for {{ $medicine->medicine_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Batch Number</label>
              <input type="text" name="batch_number" class="form-control" value="{{ $medicine->batch_number }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Supplier Name</label>
              <input type="text" name="supplier_name" class="form-control" value="{{ $medicine->supplier_name }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Action</label>
              <div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="action" id="actionRemove{{ $medicine->id }}" value="remove" checked>
                  <label class="form-check-label" for="actionRemove{{ $medicine->id }}">Return to supplier (remove from stock)</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="action" id="actionAdd{{ $medicine->id }}" value="add">
                  <label class="form-check-label" for="actionAdd{{ $medicine->id }}">Return to inventory (add to stock)</label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Returned At</label>
              <input type="datetime-local" name="returned_at" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-warning">Record Return</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endforeach

{{-- Returned Medicines List --}}
<div class="container py-4">
  <div class="card shadow-sm p-3 mt-4">
    <h5 class="mb-3">Returned Medicines</h5>
    @if(isset($returns) && $returns->isNotEmpty())
    <table class="table datatable table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Medicine</th>
            <th>Batch</th>
            <th>Quantity</th>
            <th>Supplier</th>
            <th>Remarks</th>
            <th>Returned At</th>
          </tr>
        </thead>
        <tbody>
          @foreach($returns as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ $r->medicine->medicine_name ?? '—' }}</td>
              <td>{{ $r->batch_number ?? '—' }}</td>
              <td>{{ $r->quantity }}</td>
              <td>{{ $r->supplier_name ?? '—' }}</td>
              <td>{{ $r->remarks ?? '—' }}</td>
              <td>{{ $r->returned_at ? \Carbon\Carbon::parse($r->returned_at)->format('Y-m-d H:i') : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="alert alert-secondary">No returned medicines recorded.</div>
    @endif
  </div>
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
            <label class="form-label">Supplier Name</label>
            <input type="text" name="supplier_name" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Delivered Date</label>
            <input type="date" name="delivered_date" class="form-control">
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
