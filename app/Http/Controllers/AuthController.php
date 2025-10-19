<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(){
        
        return view('auth.login'); }

    public function loginStep(Request $request, SmsService $sms)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required']);

        $user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return back()->withErrors(['email'=>'Invalid credentials']);
        }

        $code = rand(100000,999999);

        OtpCode::create([
            'user_id'=>$user->user_id,
            'code'=>$code,
            'expires_at'=>Carbon::now()->addMinutes(5),
        ]);
        if($user->phone_number){
        $sms->send($user->phone_number, "Your MediTrack OTP is: {$code}");
        }

        session(['otp_user_id'=>$user->user_id]);
        return redirect()->route('auth.otp.form')->with('status','OTP sent to your phone');
    }

    public function showOtpForm(){
         return view('auth.otp'); 
        }

    public function verifyOtp(Request $request){
        $request->validate(['otp' => 'required']);
        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $otp = OtpCode::where('user_id', $userId)
            ->where('code', $request->otp)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired()) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        $otp->update(['used' => true]);
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        Auth::login($user);
        session()->forget('otp_user_id');

        $redirectRoute = $user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard';
        
        return redirect()->route($redirectRoute)->with('success', 'Login successful!');
    }

    public function showRegister(){
         return view('auth.register'); 
        }

    public function register(Request $request){
        $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);
        
        Auth::login($user);
        
        return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard')
            ->with('success', 'Registration successful!');
    }

    public function logout(){ 
        Auth::logout(); return redirect()->route('login'); }
}
