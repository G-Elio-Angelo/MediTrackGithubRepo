<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Services\SmsService;
use App\Services\ActivityLogger;
use Carbon\Carbon;

class MedicineController extends Controller
{
    public function index(){ $meds = Medicine::paginate(10); return view('medicines.index',compact('meds')); }
    public function create(){ return view('medicines.create'); }
    
    public function edit(Medicine $medicine){ return view('medicines.edit',compact('medicine')); }
    public function update(Request $r, Medicine $medicine){ $medicine->update($r->all()); ActivityLogger::log('medicine.updated', ['medicine_id' => $medicine->id]); return redirect()->route('medicines.index'); }
    public function destroy(Medicine $medicine){ ActivityLogger::log('medicine.deleted', ['medicine_id' => $medicine->id]); $medicine->delete(); return back(); }
    public function store(Request $r){ $r->validate(['medicine_name'=>'required']); $m = Medicine::create($r->all()); ActivityLogger::log('medicine.created', ['medicine_id' => $m->id]); return redirect()->route('medicines.index'); }

    // daily check for alerts
    public function runAlertChecks(SmsService $sms){
        $low = Medicine::where('stock','=<',10)->get();
        foreach($low as $m){
            $sms->send(env('ADMIN_PHONE'), "Low stock alert: {$m->medicine_name} ({$m->stock} left)");
        }
        $exp = Medicine::whereBetween('expiry_date',[Carbon::now(), Carbon::now()->addDays(30)])->get();
        foreach($exp as $m){
            $sms->send(env('ADMIN_PHONE'), "Expiry alert: {$m->medicine_name} expires on {$m->expiry_date}");
        }
    }
}
