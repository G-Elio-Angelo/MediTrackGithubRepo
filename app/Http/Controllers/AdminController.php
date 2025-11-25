<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use App\Models\MedicineReturn;
use App\Models\Setting;
use App\Services\SmsService;
use App\Services\ActivityLogger;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function admindashb(SmsService $sms, MailService $mailService)
    {
        $users = User::all();
        $totalUsers = $users->count();
        
        $medicines = Medicine::selectRaw('medicine_name, SUM(stock) as stock, MIN(expiry_date) as expiry_date')
            ->groupBy('medicine_name')
            ->get();

        // total unique medicine names
        $totalMedicines = $medicines->count();

        // Low stock per medicine (aggregated)
        $lowStockThreshold = 10;

        $lowStock = Medicine::selectRaw('medicine_name, SUM(stock) as stock, MIN(expiry_date) as expiry_date')
            ->groupBy('medicine_name')
            ->havingRaw('SUM(stock) <= ?', [$lowStockThreshold])
            ->get();

        $lowStockCount = $lowStock->count();

        // Near-expiry: medicines whose nearest expiry is within the next 10 days
        $today = Carbon::today();
        $threshold = $today->copy()->addDays(10);

        // $medicines is aggregated with MIN(expiry_date) as expiry_date
        $nearExpiry = $medicines->filter(function ($m) use ($threshold, $today) {
            if (empty($m->expiry_date)) return false;
            try {
                $exp = Carbon::parse($m->expiry_date);
            } catch (\Exception $e) {
                return false;
            }
            return $exp->between($today, $threshold);
        })->values();

        $nearExpiryCount = $nearExpiry->count();

        // Combined notifications: if there are low-stock items OR near-expiry items, notify the logged-in admin
        if (($lowStockCount > 0 || $nearExpiryCount > 0) && Auth::check()) {
            $admin = Auth::user();

            $parts = [];
            if ($lowStockCount > 0) {
                $lowStockList = $lowStock->map(function ($l) { return $l->medicine_name . ' (' . $l->stock . ' left)'; })->implode(', ');
                $parts[] = "Low stock: {$lowStockList}";
            }
            if ($nearExpiryCount > 0) {
                $nearList = $nearExpiry->map(function ($n) { return $n->medicine_name . ' (exp: ' . $n->expiry_date . ')'; })->implode(', ');
                $parts[] = "Expiring soon: {$nearList}";
            }

            $smsMessage = "Alert! ";
            $smsMessage .= implode(' | ', $parts);

            // Send combined SMS
            try {
                $sms->send($admin->phone_number, $smsMessage);
            } catch (\Throwable $e) {
                Log::error('Failed to send combined alert SMS', ['error' => $e->getMessage()]);
            }

            // Prepare combined email
            try {
                $recipient = $admin->email ?? env('ADMIN_EMAIL', null);
                if (!empty($recipient)) {
                    $subjectParts = [];
                    if ($lowStockCount > 0) $subjectParts[] = $lowStockCount . ' low stock';
                    if ($nearExpiryCount > 0) $subjectParts[] = $nearExpiryCount . ' expiring soon';
                    $subject = 'Inventory Alert: ' . implode(' & ', $subjectParts);

                    $content = "<h3>Inventory Alerts</h3>";
                    if ($lowStockCount > 0) {
                        $content .= "<h4 style=\"color:#c00\">Low Stock</h4><ul>";
                        foreach ($lowStock as $l) {
                            $content .= "<li>" . e($l->medicine_name) . " — " . e($l->stock) . " left</li>";
                        }
                        $content .= "</ul>";
                    }
                    if ($nearExpiryCount > 0) {
                        $content .= "<h4 style=\"color:#c00\">Expiring Soon (within 10 days)</h4><ul>";
                        foreach ($nearExpiry as $n) {
                            $content .= "<li>" . e($n->medicine_name) . " — Expiry: " . e($n->expiry_date) . "</li>";
                        }
                        $content .= "</ul>";
                    }
                    $content .= "<p>Please take the necessary actions.</p>";

                    $mailService->sendEmail($recipient, $subject, $content);
                } else {
                    Log::warning('No admin email configured; combined alert email not sent', ['user_id' => Auth::id()]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send combined alert email', ['error' => $e->getMessage()]);
            }
        }
        $userNames = $users->pluck('username')->values();
        $medicineNames =  $medicines->pluck('medicine_name')->values();
        $medicineStocks = $medicines->pluck('stock')->values();
        $lowStockNames = $lowStock->pluck('medicine_name')->values();
        $lowStockValues = $lowStock->pluck('stock')->values();

        // Role counts for user distribution chart
        $userRoleCounts = [
            'admin' => $users->where('role', 'admin')->count(),
            'user' => $users->where('role', 'user')->count(),
        ];

        // Medicine nearest expiry dates (matching $medicines order)
        $medicineExpiries = $medicines->pluck('expiry_date')->values();

        // Prepare non-admin users and a medicines list (nearest-expiry batch per name) for intake panel
        $nonAdminUsers = User::where('role', '!=', 'admin')->get();

        $intakeMedicines = collect();
        $names = Medicine::select('medicine_name')->groupBy('medicine_name')->pluck('medicine_name');
        foreach ($names as $name) {
            $m = Medicine::where('medicine_name', $name)->orderBy('expiry_date', 'asc')->first();
            if ($m) $intakeMedicines->push($m);
        }

        return view('dashboard.admin', compact(
            'totalUsers', 'users',
            'totalMedicines', 'medicines',
            'lowStockCount', 'lowStock',
            'userNames', 'medicineNames', 'medicineStocks', 'lowStockNames', 'lowStockValues',
            'nonAdminUsers', 'intakeMedicines', 'userRoleCounts', 'medicineExpiries', 'nearExpiry'
        ));
    }

    public function storeIntake(Request $request)
    {
        // Log incoming request for debugging
        Log::info('storeIntake called', $request->all());

        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'medicine_id' => 'required|exists:medicines,id',
            'intake_time' => 'required|date',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = User::where('user_id', $validated['user_id'])->first();
        if (!$user || $user->role === 'admin') {
            Log::warning('storeIntake invalid target user', ['user_id' => $validated['user_id']]);
            return redirect()->back()->with('error', 'Invalid target user for intake.');
        }

        $medicine = Medicine::findOrFail($validated['medicine_id']);
        $reqInterval = $request->input('interval_minutes');
        if (!empty($reqInterval) && is_numeric($reqInterval) && (int)$reqInterval > 0) {
            $intervalMinutes = (int)$reqInterval;
        } else {
            $intervalMinutes = (int) ($medicine->intake_interval_minutes ?? Setting::getValue('intake_interval_minutes', 30));
        }
        try {
            $intakeDt = Carbon::parse($validated['intake_time']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid intake time format.');
        }
        if ($intervalMinutes > 1) {
            $minutes = (int)$intakeDt->format('i');
            $minutesTotal = $intakeDt->hour * 60 + $minutes;
            $roundedTotal = (int) (round($minutesTotal / $intervalMinutes) * $intervalMinutes);
            $newHour = intdiv($roundedTotal, 60);
            $newMin = $roundedTotal % 60;
            $intakeDt->setTime($newHour, $newMin, 0);
        }
        $validated['intake_time'] = $intakeDt->toDateTimeString();
        $qty = (int) $validated['quantity'];

        if ($medicine->stock < $qty) {
            Log::warning('storeIntake insufficient stock', ['medicine_id' => $medicine->id, 'stock' => $medicine->stock, 'requested' => $qty]);
            return redirect()->back()->with('error', 'Insufficient stock for selected medicine.');
        }

        try {
            DB::beginTransaction();

            $intake = MedicineIntake::create([
                'user_id' => $user->user_id,
                'medicine_id' => $validated['medicine_id'],
                'intake_time' => $validated['intake_time'],
                'status' => false,
                'quantity' => $qty,
            ]);

            // Log activity
            ActivityLogger::log('intake.scheduled', [
                'intake_id' => $intake->id,
                'user_id' => $user->user_id,
                'medicine_id' => $medicine->id,
                'quantity' => $qty,
                'intake_time' => $validated['intake_time'],
            ]);

            // Decrement stock
            $medicine->stock = max(0, $medicine->stock - $qty);
            $medicine->save();

            DB::commit();

            Log::info('storeIntake scheduled', ['intake_id' => $intake->id, 'medicine_id' => $medicine->id, 'remaining_stock' => $medicine->stock]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to schedule intake and update stock', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return redirect()->back()->with('error', 'Failed to schedule intake. Check logs for details.');
        }

        return redirect()->route('admin.intakes')->with('success', 'Medicine intake scheduled for user.');
    }

    // Manage users
    public function users()
    {
        $users = User::all();
        $medicines = Medicine::all();
        return view('dashboard.manage_users', compact('users', 'medicines'));
    }

    // Manage medicines
    public function medicines()
    {
        $medicines = Medicine::all();
        // include recent returns for display
        $returns = MedicineReturn::with('medicine')->orderBy('returned_at','desc')->get();
        return view('dashboard.manage_medicines', compact('medicines','returns'));
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'intake_interval_minutes' => 'nullable|integer|min:1|max:1440',
            'stock' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
        ]);

        $medicine = Medicine::create($validated);

        ActivityLogger::log('medicine.created', [
            'medicine_id' => $medicine->id,
            'name' => $medicine->medicine_name,
        ]);

        return redirect()->route('admin.medicines')->with('success', 'Medicine added successfully.');
    }

    public function updateMedicine(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'intake_interval_minutes' => 'nullable|integer|min:1|max:1440',
            'stock' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
        ]);

        $medicine->update($validated);

        ActivityLogger::log('medicine.updated', [
            'medicine_id' => $medicine->id,
        ]);

        return redirect()->route('admin.medicines')->with('success', 'Medicine updated successfully.');
    }

    public function deleteMedicine($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();

        ActivityLogger::log('medicine.deleted', ['medicine_id' => $id]);

        return redirect()->route('admin.medicines')->with('success', 'Medicine deleted successfully.');
    }

    // Activity logs page
    public function activityLogs()
    {
        // Load all logs for display; DataTables will handle client-side paging/sorting
        return view('dashboard.activity_logs');
    }

    public function activityLogsData(Request $request)
    {
        $draw = (int)$request->input('draw', 0);
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = \App\Models\ActivityLog::with('user');

        $recordsTotal = $query->count();

        if (!empty($search)) {
            $query = $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('meta', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                         ->orWhere('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $recordsFiltered = $query->count();

        // ordering
        $orderColIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'user_id', 'action', 'ip_address', 'created_at'];
        $orderCol = $columns[$orderColIndex] ?? 'created_at';
        if ($orderCol === 'user_id') {
            $query = $query->orderBy('user_id', $orderDir);
        } else {
            $query = $query->orderBy($orderCol, $orderDir);
        }

        $data = $query->skip($start)->take($length)->get();

        $rows = $data->map(function($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? ($log->user->full_name ?? $log->user->username) : 'System',
                'action' => $log->action,
                'ip_address' => $log->ip_address ?? '—',
                'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i') : '',
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    // List medicine intakes (admin view)
    public function intakes()
    {
        $intakes = MedicineIntake::with(['medicine', 'user'])->orderBy('intake_time')->get();

        $nonAdminUsers = User::where('role', '!=', 'admin')->get();

        $intakeMedicines = collect();
        $names = Medicine::select('medicine_name')->groupBy('medicine_name')->pluck('medicine_name');
        foreach ($names as $name) {
            $m = Medicine::where('medicine_name', $name)->orderBy('expiry_date', 'asc')->first();
            if ($m) $intakeMedicines->push($m);
        }

        return view('dashboard.manage_intakes', compact('intakes', 'nonAdminUsers', 'intakeMedicines'));
    }

    // Edit intake form
    public function editIntake($id)
    {
        $intake = MedicineIntake::with(['medicine', 'user'])->findOrFail($id);
        $medicines = Medicine::all();
        $users = User::where('role', '!=', 'admin')->get();
        return view('dashboard.edit_intake', compact('intake', 'medicines', 'users'));
    }

    // Update intake
    public function updateIntake(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'medicine_id' => 'required|exists:medicines,id',
            'intake_time' => 'required|date',
            'status' => 'nullable|boolean',
        ]);

        $intake = MedicineIntake::findOrFail($id);
        $intake->user_id = $validated['user_id'];
        $intake->medicine_id = $validated['medicine_id'];
        $intake->intake_time = $validated['intake_time'];
        $intake->status = $validated['status'] ?? false;
        $intake->save();

        ActivityLogger::log('intake.updated', [
            'intake_id' => $intake->id,
            'user_id' => $intake->user_id,
            'medicine_id' => $intake->medicine_id,
            'intake_time' => $intake->intake_time,
        ]);

        // If an intake_interval_minutes was submitted in the edit form, update the medicine's interval
        if ($request->filled('intake_interval_minutes')) {
            $mi = Medicine::find($validated['medicine_id']);
            if ($mi) {
                $mi->intake_interval_minutes = (int)$request->input('intake_interval_minutes');
                $mi->save();
            }
        }

        return redirect()->route('admin.intakes')->with('success', 'Intake updated successfully.');
    }

    // Delete intake
    public function deleteIntake($id)
    {
        $intake = MedicineIntake::findOrFail($id);
        ActivityLogger::log('intake.deleted', ['intake_id' => $intake->id, 'user_id' => $intake->user_id, 'medicine_id' => $intake->medicine_id]);
        $intake->delete();
        return redirect()->route('admin.intakes')->with('success', 'Intake deleted successfully.');
    }

    // Update user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:1000',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'role' => 'required|string|in:admin,user',
        ]);

        $user->username = $validated['username'];
        $user->first_name = $validated['first_name'];
        $user->middle_name = $validated['middle_name'] ?? null;
        $user->last_name = $validated['last_name'];
        $user->age = $validated['age'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->role = $validated['role'];

        $user->save();

        ActivityLogger::log('user.updated', ['user_id' => $user->user_id]);

        if ($request->filled('medicine_id') && $request->filled('intake_time')) {
            $miValidated = $request->validate([
                'medicine_id' => 'required|exists:medicines,id',
                'intake_time' => 'required|date',
            ]);

            MedicineIntake::create([
                'user_id' => $user->user_id,
                'medicine_id' => $miValidated['medicine_id'],
                'intake_time' => $miValidated['intake_time'],
                'status' => false,
            ]);

            ActivityLogger::log('intake.scheduled_by_admin', ['user_id' => $user->user_id, 'medicine_id' => $miValidated['medicine_id'], 'intake_time' => $miValidated['intake_time']]);
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    // Delete user
    public function deleteUser($id)
    {
        $u = User::findOrFail($id);
        ActivityLogger::log('user.deleted', ['user_id' => $u->user_id]);
        $u->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    

    // Record a medicine return and optionally adjust inventory
    public function returnMedicine(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:2000',
            'action' => 'required|in:remove,add',
            'batch_number' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'returned_at' => 'nullable|date',
        ]);

        $medicine = Medicine::findOrFail($id);

        try {
            DB::beginTransaction();

            $mr = MedicineReturn::create([
                'medicine_id' => $medicine->id,
                'batch_number' => $validated['batch_number'] ?? $medicine->batch_number,
                'quantity' => $validated['quantity'],
                'supplier_name' => $validated['supplier_name'] ?? $medicine->supplier_name,
                'remarks' => $validated['remarks'] ?? null,
                'returned_at' => $validated['returned_at'] ?? now(),
            ]);

            // Adjust stock: 'remove' => return to supplier (decrease stock), 'add' => return to inventory (increase)
            if ($validated['action'] === 'remove') {
                $medicine->stock = max(0, $medicine->stock - $validated['quantity']);
            } else {
                $medicine->stock = $medicine->stock + $validated['quantity'];
            }
            $medicine->save();

            DB::commit();

            Log::info('Medicine return recorded', ['return_id' => $mr->id, 'medicine_id' => $medicine->id, 'action' => $validated['action'], 'qty' => $validated['quantity']]);
            ActivityLogger::log('medicine.returned', [
                'return_id' => $mr->id,
                'medicine_id' => $medicine->id,
                'action' => $validated['action'],
                'quantity' => $validated['quantity'],
            ]);
            return redirect()->route('admin.medicines')->with('success', 'Medicine return recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to record medicine return', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return redirect()->back()->with('error', 'Failed to record return.');
        }
    }

    

}

