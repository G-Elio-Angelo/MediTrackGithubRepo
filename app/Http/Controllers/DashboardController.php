<?php
namespace App\Http\Controllers;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use App\Models\MedicineReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class DashboardController extends Controller
{
    public function index(Request $request){
        // If a normal (non-authenticated) metrics dashboard is needed
        if (!Auth::check() || Auth::user()->role !== 'user') {
            $total = Medicine::count();
            $low = Medicine::where('stock','<',10)->count();
            $expiring = Medicine::whereBetween('expiry_date',[Carbon::now(), Carbon::now()->addDays(30)])->count();
            return view('dashboard.index',compact('total','low','expiring'));
        }

        // For regular users: show their medicine intakes
        $user = Auth::user();
        $intakes = MedicineIntake::with('medicine')
            ->where('user_id', $user->user_id)
            ->orderBy('intake_time')
            ->get();

        return view('dashboard.user', compact('intakes'));
    }

    // Confirm intake action
    public function confirmIntake(Request $request, $id)
    {
        $user = Auth::user();
        $intake = MedicineIntake::with('medicine')->where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $qty = (int) ($intake->quantity ?? 1);
        $interval = (int) ($intake->medicine->intake_interval_minutes ?? \App\Models\Setting::getValue('intake_interval_minutes', 30));

        try {
            DB::beginTransaction();

            // Record confirmation time
            $intake->confirmed_at = Carbon::now();

            $remaining = max(0, $qty - 1);

            if ($remaining > 0) {
                $intake->quantity = $remaining;
                try {
                    $next = Carbon::parse($intake->intake_time)->addMinutes($interval);
                    $intake->intake_time = $next;
                } catch (\Exception $e) {
                    Log::warning('Failed to adjust intake_time on confirmation', ['error' => $e->getMessage(), 'intake_id' => $id]);
                }
                $intake->status = false; // keep pending for remaining doses
                $intake->save();
            } else {
                $intake->delete();
            }

            DB::commit();
            ActivityLogger::log('intake.confirmed', ['intake_id' => $intake->id, 'user_id' => $user->user_id, 'medicine_id' => $intake->medicine->id ?? null, 'quantity_taken' => 1]);
            return redirect()->back()->with('success', 'Intake confirmed.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to confirm intake', ['error' => $e->getMessage(), 'intake_id' => $id]);
            return redirect()->back()->with('error', 'Failed to confirm intake.');
        }
    }

    // Patient returns medicine from a scheduled intake
    public function returnIntake(Request $request, $id)
    {
        $user = Auth::user();
        $intake = MedicineIntake::with('medicine')->where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $qty = (int)$validated['quantity'];

        if ($qty > ($intake->quantity ?? 0)) {
            return redirect()->back()->with('error', 'Return quantity cannot exceed the amount you have.');
        }

        $medicine = $intake->medicine;
        if (!$medicine) {
            return redirect()->back()->with('error', 'Associated medicine not found.');
        }

        try {
            DB::beginTransaction();

            // Create a return record (added back to inventory)
            $mr = MedicineReturn::create([
                'medicine_id' => $medicine->id,
                'batch_number' => $medicine->batch_number,
                'quantity' => $qty,
                'supplier_name' => $medicine->supplier_name,
                'remarks' => $validated['remarks'] ?? null,
                'returned_at' => now(),
            ]);

            // Increase medicine stock
            $medicine->stock = $medicine->stock + $qty;
            $medicine->save();

            // Decrease or remove intake quantity
            $remaining = ($intake->quantity ?? 0) - $qty;
            if ($remaining <= 0) {
                $intake->delete();
            } else {
                $intake->quantity = $remaining;
                $intake->save();
            }

            DB::commit();
            Log::info('Patient returned medicine', ['user_id' => $user->user_id, 'medicine_id' => $medicine->id, 'qty' => $qty]);
            ActivityLogger::log('intake.returned', ['user_id' => $user->user_id, 'medicine_id' => $medicine->id, 'quantity' => $qty, 'intake_id' => $intake->id]);
            return redirect()->back()->with('success', 'Return recorded — inventory updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed patient return', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return redirect()->back()->with('error', 'Failed to record return.');
        }
    }
}
