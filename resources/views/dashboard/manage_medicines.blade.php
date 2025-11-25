@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
{{-- NOTE: Assuming you have Font Awesome Pro configured or use the free version. The icons below use 'fal' (light) or 'far' (regular) for a more minimal look. If you don't have it, change 'fal' to 'fas'. --}}
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" xintegrity="sha384-AYmEC3Yw5U5dD7i7t53X30m46O7hF6+I23g9M+B4hL2t8F9gYt5Tz30O+gR7/d6KxYj" crossorigin="anonymous">


<div class="mtk-app">

    {{-- SIDEBAR --}}
    <aside class="mtk-sidebar">
        <div class="mtk-brand">
           <h1 class="mtk-brand-title">MediTrack</h1>
        </div>

        <nav class="mtk-nav">
            <a href="{{ route('admin.dashboard') }}" class="mtk-nav-item">
                <i class="fal fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.medicines') }}" class="mtk-nav-item active">
                <i class="fal fa-box-alt"></i><span>Inventory (Medicines)</span>
            </a>
            <a href="{{ route('admin.users') }}" class="mtk-nav-item">
                <i class="fal fa-users-crown"></i><span>Admin & User Records</span>
            </a>
            <a href="{{ route('admin.logs') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Activity Reports</span>
            </a>
        </nav>

        {{-- START: Updated Sidebar Footer Section --}}
        <div class="mtk-sidebar-bottom-actions">
            {{-- Logout Form placed prominently at the bottom --}}
            <form method="POST" action="{{ route('logout') }}" class="w-100 mb-2">
                @csrf
                {{-- Using mtk-btn-secondary for consistent theme styling, but full width (w-100) --}}
                <button type="submit" class="mtk-btn-secondary w-100">
                    <i class="fal fa-sign-out"></i> Logout
                </button>
            </form>
        </div>
        {{-- END: Updated Sidebar Footer Section --}}
    </aside>


    <main class="mtk-main">
        {{-- TOPBAR --}}
        <header class="mtk-topbar">
            <div class="mtk-search">
                <i class="fal fa-search mtk-search-icon"></i>
                <input type="text" placeholder="Search inventory..." aria-label="Search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    {{-- Assuming first_name is available, showing first two letters, e.g., MI for Michael I --}}
                    <div class="mtk-avatar">{{ strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name ?? '', 0, 1)) }}</div> 
                    <div class="mtk-user-meta">
                        <div class="mtk-user-name">{{ Auth::user()->first_name ?? 'Admin' }}</div>
                    </div>
                </div>
                @endauth
            </div>
        </header>
        {{-- END TOPBAR --}}

        <section class="mtk-content">
            <div class="mtk-hero">
                <h2>Manage Medicine Inventory <i class="fal fa-box-check"></i></h2>
                <p class="muted">View, track, and manage all medicine stocks, batches, and expiry dates.</p>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
                <div class="alert alert-success mtk-alert"><i class="fal fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            {{-- CURRENT STOCK TABLE --}}
            <div class="panel">
                <div class="panel-head">
                    <h5 class="panel-title">Current Medicine Stock</h5>
                    <div class="mtk-actions m-0 p-0">
                       <a href="{{ route('admin.reports') }}" class="mtk-btn-secondary"><i class="fal fa-chart-bar"></i> Reports</a>
                        <button class="mtk-btn" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
                            <i class="fal fa-plus-circle"></i> Add New Medicine
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle datatable">
                            <thead>
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
                                        {{-- Low stock warning --}}
                                        <td class="{{ $medicine->stock < 10 ? 'text-danger fw-bold' : '' }}">{{ $medicine->stock }}</td>
                                        <td>{{ $medicine->delivered_date ? \Carbon\Carbon::parse($medicine->delivered_date)->format('Y-m-d') : '—' }}</td>
                                        <td>
                                            {{-- Expiring soon warning (30 days) --}}
                                            <span class="{{ \Carbon\Carbon::parse($medicine->expiry_date)->diffInDays(\Carbon\Carbon::now()) <= 30 && \Carbon\Carbon::parse($medicine->expiry_date)->isFuture() ? 'text-warning fw-bold' : '' }} 
                                            {{ \Carbon\Carbon::parse($medicine->expiry_date)->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ $medicine->expiry_date }}
                                            </span>
                                        </td>
                                        <td class="mtk-table-actions">
                                            {{-- Edit Button (Icon Button) --}}
                                            <button 
                                                type="button" 
                                                class="icon-btn text-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMedicineModal{{ $medicine->id }}"
                                                title="Edit Details">
                                                <i class="fal fa-pencil-alt"></i>
                                            </button>

                                            {{-- Return/Restock Button (Icon Button) --}}
                                            <button 
                                                type="button"
                                                class="icon-btn text-warning me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#returnMedicineModal{{ $medicine->id }}"
                                                title="Record Return/Restock">
                                                <i class="fal fa-undo"></i>
                                            </button>

                                            {{-- Delete Form (Icon Button) --}}
                                            <form action="{{ route('admin.medicines.delete', $medicine->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-btn text-danger" title="Delete Record" onclick="return confirm('Are you sure you want to permanently delete this medicine record?')">
                                                    <i class="fal fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            ---

            {{-- RETURN HISTORY PANEL --}}
            <div class="panel mt-4">
                <div class="panel-head">
                    <h5 class="panel-title">Returned Medicines History</h5>
                </div>
                <div class="panel-body">
                    {{-- Check if $returns variable is set and has content --}}
                    @if(isset($returns) && $returns->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Medicine</th>
                                    <th>Batch</th>
                                    <th>Quantity</th>
                                    <th>Action Type</th> {{-- Added action type column for clarity --}}
                                    <th>Supplier</th>
                                    <th>Remarks</th>
                                    <th>Action Date</th> {{-- Renamed Returned At for clarity --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returns as $r)
                                    <tr>
                                        <td>{{ $r->id }}</td>
                                        <td>{{ $r->medicine->medicine_name ?? '—' }}</td>
                                        <td>{{ $r->batch_number ?? '—' }}</td>
                                        <td>{{ $r->quantity }}</td>
                                        {{-- Display action type based on value (assuming a column 'action_type' exists) --}}
                                        <td>
                                            @if($r->action_type == 'remove')
                                                <span class="badge bg-danger"><i class="fal fa-minus-circle"></i> Removal</span>
                                            @elseif($r->action_type == 'add')
                                                <span class="badge bg-success"><i class="fal fa-plus-circle"></i> Restock</span>
                                            @else
                                                <span class="badge bg-secondary">Unknown</span>
                                            @endif
                                        </td>
                                        <td>{{ $r->supplier_name ?? '—' }}</td>
                                        <td>{{ $r->remarks ?? '—' }}</td>
                                        <td>{{ $r->returned_at ? \Carbon\Carbon::parse($r->returned_at)->format('Y-m-d H:i') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="alert alert-info mtk-alert">
                            <i class="fal fa-info-circle"></i> No returned or restocked medicine transactions recorded yet.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>


{{-- ADD MEDICINE MODAL --}}
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-labelledby="addMedicineLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mtk-modal-content">
            <form action="{{ route('admin.medicines.store') }}" method="POST">
                @csrf
                <div class="modal-header mtk-modal-header">
                    <h5 class="modal-title" id="addMedicineLabel"><i class="fal fa-plus-circle"></i> Add New Medicine to Inventory</h5>
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
                <div class="modal-footer mtk-modal-footer">
                    <button type="button" class="mtk-btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="mtk-btn"><i class="fal fa-box-check"></i> Add Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MEDICINE MODALS (One for each medicine) --}}
@foreach($medicines as $medicine)
    <div class="modal fade" id="editMedicineModal{{ $medicine->id }}" tabindex="-1" aria-labelledby="editMedicineLabel{{ $medicine->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content mtk-modal-content">
                <form action="{{ route('admin.medicines.update', $medicine->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header mtk-modal-header">
                        <h5 class="modal-title" id="editMedicineLabel{{ $medicine->id }}"><i class="fal fa-edit"></i> Edit {{ $medicine->medicine_name }}</h5>
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
                    <div class="modal-footer mtk-modal-footer">
                        <button type="button" class="mtk-btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="mtk-btn"><i class="fal fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- RETURN/RESTOCK MEDICINE MODALS (One for each medicine) --}}
@foreach($medicines as $medicine)
  <div class="modal fade" id="returnMedicineModal{{ $medicine->id }}" tabindex="-1" aria-labelledby="returnMedicineLabel{{ $medicine->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content mtk-modal-content">
        {{-- IMPORTANT: The route for return/restock must handle the quantity and action type to update the stock --}}
        <form action="{{ route('admin.medicines.return', $medicine->id) }}" method="POST">
          @csrf
          <div class="modal-header mtk-modal-header">
            <h5 class="modal-title" id="returnMedicineLabel{{ $medicine->id }}"><i class="fal fa-undo-alt"></i> Record Return/Restock: {{ $medicine->medicine_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Batch Number</label>
              <input type="text" name="batch_number" class="form-control" value="{{ $medicine->batch_number }}" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Supplier Name</label>
              <input type="text" name="supplier_name" class="form-control" value="{{ $medicine->supplier_name }}" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" class="form-control" min="1" max="{{ $medicine->stock }}" value="1" required>
              <small class="form-text text-muted">Max quantity for removal/return: {{ $medicine->stock }}</small>
            </div>
            <div class="mb-3">
              <label class="form-label">Action Type</label>
              <div>
                <div class="form-check form-check-inline">
                  {{-- Action: Remove from Stock --}}
                  <input class="form-check-input" type="radio" name="action_type" id="actionRemove{{ $medicine->id }}" value="remove" checked> 
                  <label class="form-check-label" for="actionRemove{{ $medicine->id }}"><i class="fal fa-minus-circle text-danger"></i> Remove from Stock (Return to Supplier/Disposal)</label>
                </div>
                <div class="form-check form-check-inline">
                  {{-- Action: Add to Stock --}}
                  <input class="form-check-input" type="radio" name="action_type" id="actionAdd{{ $medicine->id }}" value="add">
                  <label class="form-check-label" for="actionAdd{{ $medicine->id }}"><i class="fal fa-plus-circle text-success"></i> Add to Stock (Patient Return/Restock)</label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for return/restock..."></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Date/Time of Action</label>
              {{-- Pre-fill with current date/time --}}
              <input type="datetime-local" name="returned_at" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
            </div>
          </div>
          <div class="modal-footer mtk-modal-footer">
            <button type="button" class="mtk-btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="mtk-btn-warning"><i class="fal fa-arrow-alt-circle-right"></i> Record Action</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endforeach

@endsection