@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <h1 class="fw-bolder text-dark">📋 Monthly Inventory Report</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.medicines') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Inventory List
            </a>
            <a href="{{ route('admin.reports') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-chart-bar me-2"></i> All Reports
            </a>
        </div>
    </div>

    <div class="card p-4 shadow-lg rounded-xl mb-5 bg-white">
        <h4 class="card-title fw-semibold mb-3 border-bottom pb-2">Generate Analytics</h4>
        <div class="row align-items-end g-3">
            {{-- Month Selector --}}
            <div class="col-md-3">
                <label for="reportMonth" class="form-label fw-bold text-secondary">Select Reporting Month</label>
                <input type="month" id="reportMonth" class="form-control form-control-lg rounded" value="{{ $month }}">
            </div>

            <div class="col-md-9 d-flex gap-3 pt-3 pt-md-0">
                <button id="generateBtn" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-sm flex-grow-1 flex-md-grow-0" style="min-width: 200px;">
                    <i class="fas fa-sync-alt me-2"></i> Generate Analytics
                </button>
                <a id="exportCsv" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-file-csv me-2"></i> Export CSV
                </a>
                <a id="exportPdf" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card p-4 shadow-lg rounded-xl bg-white">
        <h4 class="card-title fw-semibold mb-4 border-bottom pb-2">Report Data Overview</h4>
        <div id="reportResults">
            <div class="alert alert-info text-center py-4 rounded-lg">
                <h5 class="mb-0 fw-bold text-info">👈 Select a month and click "Generate Analytics" to view the results.</h5>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
   
    function renderResults(data) {
        var html = '';

        // --- Stock Usage Table ---
        html += '<div class="mb-5">';
        html += '<h5 class="fw-bold text-primary mb-3"><i class="fas fa-chart-line me-2"></i> Stock Usage</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-striped table-hover align-middle"><thead><tr class="table-light"><th>Medicine Name</th><th>Quantity Used</th></tr></thead><tbody>';
        data.usage.forEach(function(u) {
            html += '<tr><td>' + (u.medicine_name || '—') + '</td><td class="fw-bold text-success">' + (u.used || 0) + '</td></tr>';
        });
        html += '</tbody></table>';
        html += '</div>';
        html += '</div>';

        // --- Shortages Table ---
        html += '<div class="mb-5">';
        html += '<h5 class="fw-bold text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i> Shortages </h5>';
        if (data.shortages.length === 0) {
            html += '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> All good! No shortages reported this month.</div>';
        } else {
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped table-hover align-middle"><thead><tr class="table-light"><th>Medicine Name</th><th>Current Stock</th></tr></thead><tbody>';
            data.shortages.forEach(function(s) {
                html += '<tr><td>' + s.medicine_name + '</td><td class="fw-bold text-danger">' + s.stock + '</td></tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
        }
        html += '</div>';

        // --- Expiries Table ---
        html += '<div class="mb-5">';
        html += '<h5 class="fw-bold text-warning mb-3"><i class="fas fa-calendar-times me-2"></i> Expiries This Month</h5>';
        if (data.expiries.length === 0) {
            html += '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> No medicines are expiring this month.</div>';
        } else {
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped table-hover align-middle"><thead><tr class="table-light"><th>Medicine Name</th><th>Batch Number</th><th>Expiry Date</th></tr></thead><tbody>';
            data.expiries.forEach(function(e) {
                html += '<tr><td>' + e.medicine_name + '</td><td>' + (e.batch_number || '—') + '</td><td class="text-danger fw-bold">' + e.expiry_date + '</td></tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
        }
        html += '</div>';
        
        // --- Returned Medicines Table ---
        html += '<div class="mb-5">';
        html += '<h5 class="fw-bold text-secondary mb-3"><i class="fas fa-undo me-2"></i> Returned Medicines</h5>';
        if (!data.returns || data.returns.length === 0) {
            html += '<div class="alert alert-secondary"><i class="fas fa-minus-circle me-2"></i> No returned medicines recorded this month.</div>';
        } else {
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped table-hover align-middle"><thead><tr class="table-light"><th>Medicine Name</th><th>Batch Number</th><th>Quantity</th><th>Supplier</th><th>Returned At</th></tr></thead><tbody>';
            data.returns.forEach(function(r) {
                html += '<tr><td>' + r.medicine_name + '</td><td>' + (r.batch_number || '—') + '</td><td class="fw-bold">' + r.quantity + '</td><td>' + (r.supplier_name || '—') + '</td><td>' + (r.returned_at || '—') + '</td></tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
        }
        html += '</div>';


        document.getElementById('reportResults').innerHTML = html;
    }

    $(function() {
        function updateExportLinks(month) {
            $('#exportCsv').attr('href', "{{ route('admin.reports.export') }}?format=csv&month=" + encodeURIComponent(month));
            $('#exportPdf').attr('href', "{{ route('admin.reports.export') }}?format=pdf&month=" + encodeURIComponent(month));
        }

        var initialMonth = $('#reportMonth').val();
        updateExportLinks(initialMonth);

        $('#reportMonth').on('change', function() {
            updateExportLinks($(this).val());
        });

        $('#generateBtn').on('click', function() {
            var month = $('#reportMonth').val();
            $('#generateBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Generating...');
            
            document.getElementById('reportResults').innerHTML = '<div class="text-center py-5 text-secondary"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-3">Fetching data...</p></div>';

            $.get("{{ route('admin.reports.data') }}", { month: month })
                .done(function(res) {
                    renderResults(res);
                    updateExportLinks(month);
                })
                .fail(function(err) {
                    console.error(err);
                    document.getElementById('reportResults').innerHTML = '<div class="alert alert-danger text-center"><i class="fas fa-times-circle me-2"></i> Failed to generate analytics. Please check the console for errors or try again.</div>';
                })
                .always(function() {
                    $('#generateBtn').prop('disabled', false).html('<i class="fas fa-sync-alt me-2"></i> Generate Analytics');
                });
        });
    });
</script>
@endsection