@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Monthly Inventory Reports</h2>
    <div>
      <a href="{{ route('admin.medicines') }}" class="btn btn-secondary me-2">← Back to Medicines</a>
      <a href="{{ route('admin.reports') }}" class="btn btn-outline-primary me-2">Reports</a>
    </div>
  </div>

  <div class="card p-3 shadow-sm">
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Select Month</label>
        <input type="month" id="reportMonth" class="form-control" value="{{ $month }}">
      </div>
      <div class="col-md-8 d-flex align-items-end">
        <button id="generateBtn" class="btn btn-primary me-2">Generate Analytics</button>
        <a id="exportCsv" class="btn btn-outline-success me-2" href="#">Export CSV</a>
        <a id="exportPdf" class="btn btn-outline-secondary" href="#">Export PDF</a>
      </div>
    </div>

    <div id="reportResults">
      <h5>Results will appear here after generating the analytics.</h5>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function renderResults(data) {
    var html = '';
    html += '<h5>Stock Usage</h5>';
    html += '<table class="table table-sm table-striped"><thead><tr><th>Medicine</th><th>Used</th></tr></thead><tbody>';
    data.usage.forEach(function(u) {
      html += '<tr><td>' + (u.medicine_name || '—') + '</td><td>' + (u.used || 0) + '</td></tr>';
    });
    html += '</tbody></table>';

    html += '<h5>Shortages (<= 10)</h5>';
    if (data.shortages.length === 0) {
      html += '<div class="alert alert-secondary">No shortages</div>';
    } else {
      html += '<table class="table table-sm table-striped"><thead><tr><th>Medicine</th><th>Stock</th></tr></thead><tbody>';
      data.shortages.forEach(function(s) {
        html += '<tr><td>' + s.medicine_name + '</td><td>' + s.stock + '</td></tr>';
      });
      html += '</tbody></table>';
    }

    html += '<h5>Expiries This Month</h5>';
    if (data.expiries.length === 0) {
      html += '<div class="alert alert-secondary">No expiries this month</div>';
    } else {
      html += '<table class="table table-sm table-striped"><thead><tr><th>Medicine</th><th>Batch</th><th>Expiry Date</th></tr></thead><tbody>';
      data.expiries.forEach(function(e) {
        html += '<tr><td>' + e.medicine_name + '</td><td>' + (e.batch_number || '—') + '</td><td>' + e.expiry_date + '</td></tr>';
      });
      html += '</tbody></table>';
    }

    html += '<h5>Returned Medicines</h5>';
    if (!data.returns || data.returns.length === 0) {
      html += '<div class="alert alert-secondary">No returned medicines this month</div>';
    } else {
      html += '<table class="table table-sm table-striped"><thead><tr><th>Medicine</th><th>Batch</th><th>Quantity</th><th>Supplier</th><th>Returned At</th></tr></thead><tbody>';
      data.returns.forEach(function(r) {
        html += '<tr><td>' + r.medicine_name + '</td><td>' + (r.batch_number || '—') + '</td><td>' + r.quantity + '</td><td>' + (r.supplier_name || '—') + '</td><td>' + (r.returned_at || '—') + '</td></tr>';
      });
      html += '</tbody></table>';
    }

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
      $('#generateBtn').prop('disabled', true).text('Generating...');
      $.get("{{ route('admin.reports.data') }}", { month: month })
        .done(function(res) {
          renderResults(res);
          updateExportLinks(month);
        })
        .fail(function(err) {
          console.error(err);
          alert('Failed to generate analytics. See console for details.');
        })
        .always(function() {
          $('#generateBtn').prop('disabled', false).text('Generate Analytics');
        });
    });
  });
</script>
@endsection
