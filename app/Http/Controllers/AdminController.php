<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicine;
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
        return view('dashboard.manage_users', compact('users'));
    }

    // Manage medicines
    public function medicines()
    {
        $medicines = Medicine::all();
        return view('dashboard.manage_medicines', compact('medicines'));
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
        Medicine::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Medicine deleted successfully.');
    }
    
}

