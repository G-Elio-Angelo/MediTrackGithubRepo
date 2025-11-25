<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use App\Models\MedicineReturn;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;

class ReportsController extends Controller
{
    // Show reports page
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        return view('dashboard.reports', compact('month'));
    }

    // Return JSON data for a given month (YYYY-MM)
    public function data(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } catch (\Exception $e) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $month = Carbon::now()->format('Y-m');
        }

        // Stock usage: sum of quantities scheduled/created during the month grouped by medicine
        $usageRows = MedicineIntake::select('medicine_id', DB::raw('SUM(quantity) as used'))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('medicine_id')
            ->get();

        $usage = [];
        foreach ($usageRows as $r) {
            $m = Medicine::find($r->medicine_id);
            $usage[] = [
                'medicine_id' => $r->medicine_id,
                'medicine_name' => $m ? $m->medicine_name : 'Unknown',
                'used' => (int)$r->used,
            ];
        }

        // Shortages: aggregated stock per medicine name that is below threshold
        $threshold = 10;
        $shortages = Medicine::select('medicine_name', DB::raw('SUM(stock) as stock'))
            ->groupBy('medicine_name')
            ->havingRaw('SUM(stock) <= ?', [$threshold])
            ->get()
            ->map(function($m) {
                return ['medicine_name' => $m->medicine_name, 'stock' => (int)$m->stock];
            })->values();

        // Expiries within the month
        $expiries = Medicine::whereBetween('expiry_date', [$start->toDateString(), $end->toDateString()])
            ->get(['id', 'medicine_name', 'batch_number', 'expiry_date'])
            ->map(function($m) {
                return ['id' => $m->id, 'medicine_name' => $m->medicine_name, 'batch_number' => $m->batch_number, 'expiry_date' => $m->expiry_date];
            })->values();

        // Returns within the month
        $returns = MedicineReturn::whereBetween('returned_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->with('medicine')
            ->get()
            ->map(function($r) {
                return [
                    'id' => $r->id,
                    'medicine_name' => $r->medicine ? $r->medicine->medicine_name : 'Unknown',
                    'batch_number' => $r->batch_number,
                    'quantity' => (int) $r->quantity,
                    'supplier_name' => $r->supplier_name,
                    'returned_at' => $r->returned_at ? $r->returned_at->format('Y-m-d H:i') : null,
                    'remarks' => $r->remarks,
                ];
            })->values();

        return response()->json([
            'month' => $month,
            'usage' => $usage,
            'shortages' => $shortages,
            'expiries' => $expiries,
            'returns' => $returns,
        ]);
    }

    // Export report (format: csv or pdf)
    public function export(Request $request)
    {
        $format = strtolower($request->input('format', 'csv'));
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } catch (\Exception $e) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $month = Carbon::now()->format('Y-m');
        }

        // reuse logic from data()
        $usageRows = MedicineIntake::select('medicine_id', DB::raw('SUM(quantity) as used'))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('medicine_id')
            ->get();

        $usage = [];
        foreach ($usageRows as $r) {
            $m = Medicine::find($r->medicine_id);
            $usage[] = [
                'medicine_name' => $m ? $m->medicine_name : 'Unknown',
                'used' => (int)$r->used,
            ];
        }

        $threshold = 10;
        $shortages = Medicine::select('medicine_name', DB::raw('SUM(stock) as stock'))
            ->groupBy('medicine_name')
            ->havingRaw('SUM(stock) <= ?', [$threshold])
            ->get()
            ->map(function($m) {
                return ['medicine_name' => $m->medicine_name, 'stock' => (int)$m->stock];
            })->values();

        $expiries = Medicine::whereBetween('expiry_date', [$start->toDateString(), $end->toDateString()])
            ->get(['medicine_name', 'batch_number', 'expiry_date'])
            ->map(function($m) {
                return ['medicine_name' => $m->medicine_name, 'batch_number' => $m->batch_number, 'expiry_date' => $m->expiry_date];
            })->values();

        $returns = MedicineReturn::whereBetween('returned_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->with('medicine')
            ->get()
            ->map(function($r) {
                return [
                    'medicine_name' => $r->medicine ? $r->medicine->medicine_name : 'Unknown',
                    'batch_number' => $r->batch_number,
                    'quantity' => (int)$r->quantity,
                    'supplier_name' => $r->supplier_name,
                    'returned_at' => $r->returned_at ? $r->returned_at->format('Y-m-d H:i') : null,
                    'remarks' => $r->remarks,
                ];
            })->values();

        if ($format === 'csv' || $format === 'excel') {
            $filename = "inventory_report_{$month}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($usage, $shortages, $expiries, $returns, $month) {
                $out = fopen('php://output', 'w');
                // Header
                fputcsv($out, ["Inventory Report", $month]);
                fputcsv($out, []);

                fputcsv($out, ['Stock Usage']);
                fputcsv($out, ['Medicine', 'Used Quantity']);
                foreach ($usage as $u) fputcsv($out, [$u['medicine_name'], $u['used']]);
                fputcsv($out, []);

                fputcsv($out, ['Shortages (<= 10)']);
                fputcsv($out, ['Medicine', 'Stock']);
                foreach ($shortages as $s) fputcsv($out, [$s['medicine_name'], $s['stock']]);
                fputcsv($out, []);

                fputcsv($out, ['Expiries This Month']);
                fputcsv($out, ['Medicine', 'Batch', 'Expiry Date']);
                foreach ($expiries as $e) fputcsv($out, [$e['medicine_name'], $e['batch_number'], $e['expiry_date']]);
                fputcsv($out, []);

                fputcsv($out, ['Returned Medicines']);
                fputcsv($out, ['Medicine', 'Batch', 'Quantity', 'Supplier', 'Returned At', 'Remarks']);
                foreach ($returns as $r) fputcsv($out, [$r['medicine_name'], $r['batch_number'], $r['quantity'], $r['supplier_name'], $r['returned_at'], $r['remarks']]);

                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        // PDF export: try to use Dompdf if available, otherwise return HTML view
        $html = view('dashboard.report_pdf', compact('usage', 'shortages', 'expiries', 'returns', 'month'))->render();

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
            $filename = "inventory_report_{$month}.pdf";
            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Fallback: return HTML that can be printed to PDF by the browser
        return response($html);
    }
}
