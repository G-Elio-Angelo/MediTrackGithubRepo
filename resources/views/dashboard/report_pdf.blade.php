<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Inventory Report - {{ $month }}</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
    h2 { margin-bottom: 6px; }
    table { width:100%; border-collapse: collapse; margin-bottom: 12px; }
    th, td { border:1px solid #ccc; padding:6px; text-align:left; }
    th { background:#f2f2f2; }
    .section { margin-bottom: 16px; }
  </style>
</head>
<body>
  <h2>Inventory Report — {{ $month }}</h2>

  <div class="section">
    <h3>Stock Usage</h3>
    <table>
      <thead>
        <tr><th>Medicine</th><th>Used Quantity</th></tr>
      </thead>
      <tbody>
        @foreach($usage as $u)
          <tr>
            <td>{{ $u['medicine_name'] }}</td>
            <td>{{ $u['used'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section">
    <h3>Shortages (<= 10)</h3>
    <table>
      <thead>
        <tr><th>Medicine</th><th>Stock</th></tr>
      </thead>
      <tbody>
        @foreach($shortages as $s)
          <tr>
            <td>{{ $s['medicine_name'] }}</td>
            <td>{{ $s['stock'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section">
    <h3>Expiries This Month</h3>
    <table>
      <thead>
        <tr><th>Medicine</th><th>Batch</th><th>Expiry Date</th></tr>
      </thead>
      <tbody>
        @foreach($expiries as $e)
          <tr>
            <td>{{ $e['medicine_name'] }}</td>
            <td>{{ $e['batch_number'] }}</td>
            <td>{{ $e['expiry_date'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section">
    <h3>Returned Medicines</h3>
    <table>
      <thead>
        <tr>
            <th>Medicine</th>
            <th>Batch</th>
            <th>Quantity</th>
            <th>Supplier</th>
            <th>Returned At</th>
            <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @foreach($returns as $r)
          <tr>
            <td>{{ $r['medicine_name'] }}</td>
            <td>{{ $r['batch_number'] }}</td>
            <td>{{ $r['quantity'] }}</td>
            <td>{{ $r['supplier_name'] }}</td>
            <td>{{ $r['returned_at'] }}</td>
            <td>{{ $r['remarks'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</body>
</html>