<?php
namespace App\Http\Controllers;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $intake = MedicineIntake::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $intake->status = true;
        $intake->confirmed_at = Carbon::now();
        $intake->save();

        return redirect()->back()->with('success', 'Intake confirmed.');
    }
}
