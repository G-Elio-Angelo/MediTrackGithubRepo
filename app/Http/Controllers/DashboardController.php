<?php
namespace App\Http\Controllers;
use App\Models\Medicine;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(){
        $total = Medicine::count();
        $low = Medicine::where('stock','<',10)->count();
        $expiring = Medicine::whereBetween('expiry_date',[Carbon::now(), Carbon::now()->addDays(30)])->count();
        return view('dashboard.index',compact('total','low','expiring'));
    }
}
