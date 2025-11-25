<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - {{ $month }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; margin: 20px; }
        h2 { margin-bottom: 6px; color: #1f3a93; text-align: center; }
        h3 { border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-top: 15px; margin-bottom: 8px; font-size: 14px; }
        .section { margin-bottom: 16px; page-break-inside: avoid; }
        table { width:100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border:1px solid #dcdcdc; padding: 8px; text-align:left; vertical-align: top; }
            th { 
            background:#1f3a99; 
            color: white; 
            text-transform: uppercase;
            font-size: 11px;
            text-align: center;
        }
                .summary-box {
            border: 2px solid #1f3a99;
            padding: 10px 20px;
            margin-bottom: 20px;
            background-color: #e6f0ff;
            display: block; 
        }
        .summary-box strong { font-size: 16px; color: #1f3a99; }
        .summary-box p { margin: 3px 0; }
                .align-center { text-align: center; }
    </style>
</head>
<body>
    
    <h2>💊 Inventory Report — {{ $month }} 📅</h2>

    <div class="summary-box">
        <p>This report summarizes inventory activities and key alerts for the period of <strong>{{ $month }}</strong>.</p>
        <p>Total items used: **{{ array_sum(array_column($usage, 'used')) }}**</p>
        <p>Shortage alerts triggered: **{{ count($shortages) }}**</p>
        <p>Medicines expiring this month: **{{ count($expiries) }}**</p>
    </div>

    <div class="section">
        <h3>Stock Usage Analysis</h3>
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th class="align-center">Used Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usage as $u)
                    <tr>
                        <td>{{ $u['medicine_name'] }}</td>
                        <td class="align-center">{{ $u['used'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>⚠️ Shortages (Stock Quantity $\le$ 10)</h3>
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th class="align-center">Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shortages as $s)
                    <tr>
                        <td>{{ $s['medicine_name'] }}</td>
                        <td class="align-center">{{ $s['stock'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>🚨 Expiries This Month</h3>
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Batch</th>
                    <th class="align-center">Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiries as $e)
                    <tr>
                        <td>{{ $e['medicine_name'] }}</td>
                        <td>{{ $e['batch_number'] }}</td>
                        <td class="align-center">{{ $e['expiry_date'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>🔙 Returned Medicines</h3>
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Batch</th>
                    <th class="align-center">Quantity</th>
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
                        <td class="align-center">{{ $r['quantity'] }}</td>
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