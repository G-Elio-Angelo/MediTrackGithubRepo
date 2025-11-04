<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function admindashb(SmsService $sms)
    {
        $users = User::all();
        $totalUsers = $users->count();
        
        $medicines = Medicine::all();
        $totalMedicines = $medicines->count();

        $lowStock = Medicine::where('stock', '<', 10)->get();
        $lowStockCount = $lowStock->count();

        if($lowStockCount > 0 && Auth::check()){
            $admin = Auth::user();
            $lowStockList = $lowStock->pluck('medicine_name')->implode(', ');
            $message = " Low stock alert! \nThe following medicine are running low: {$lowStockList},";
            $sms->send($admin->phone_number, $message);
        }
        $userNames = $users->pluck('username')->values();
        $medicineNames =  $medicines->pluck('medicine_name')->values();
        $medicineStocks = $medicines->pluck('stock')->values();
        $lowStockNames = $lowStock->pluck('medicine_name')->values();
        $lowStockValues = $lowStock->pluck('stock')->values();

        return view('dashboard.admin', compact(
            'totalUsers', 'users',
            'totalMedicines', 'medicines',
            'lowStockCount', 'lowStock',
            'userNames', 'medicineNames', 'medicineStocks', 'lowStockNames', 'lowStockValues'
        ));
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
        return view('dashboard.manage_medicines', compact('medicines'));
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

        // Optionally assign a medicine intake for the user (admin can set medicine + intake time)
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
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    // Delete user
    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // Delete medicine
    public function deleteMedicine($id)
{
    $medicine = Medicine::findOrFail($id);
    $medicine->delete();

    return redirect()->route('admin.medicines')->with('success', 'Medicine deleted successfully.');
}

    // Update medicine
   public function updateMedicine(Request $request, $id)
{
    $medicine = Medicine::findOrFail($id);

    $medicine->update($request->validate([
        'medicine_name' => 'required|string|max:255',
        'batch_number' => 'required|string|max:255',
        'stock' => 'required|integer|min:0',
        'expiry_date' => 'required|date',
    ]));

    return redirect()->route('admin.medicines')->with('success', 'Medicine updated successfully.');
}
    // Store medicine
    public function storeMedicine(Request $request)
{
    $validated = $request->validate([
        'medicine_name' => 'required|string|max:255',
        'batch_number' => 'required|string|max:255',
        'stock' => 'required|integer|min:0',
        'expiry_date' => 'required|date',
    ]);
    Medicine::create($validated);
    return redirect()->route('admin.medicines')->with('success', 'Medicine added successfully.');
}

}

