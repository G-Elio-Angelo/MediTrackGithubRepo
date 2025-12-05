<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (!session()->isStarted()) {
            session()->start();
        }
        session()->regenerateToken();

        return view('auth.login');
    }

    public function loginStep(Request $request, SmsService $sms)
    {
        // Log debug info to diagnose CSRF/session issues
        Log::info('loginStep debug start', [
            'has_token' => $request->has('_token'),
            'request_token' => $request->_token ?? null,
            'session_token' => session()->token(),
            'session_id' => session()->getId(),
            'ip' => $request->ip(),
        ]);

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
        $sendResult = null;
        if($user->phone_number){
            $sendResult = $sms->send($user->phone_number, "Your MediTrack OTP is: {$code}");
        }
        if (is_array($sendResult) && isset($sendResult['provider']) && $sendResult['provider'] === 'local-log') {
            session()->flash('debug_otp', $code);
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

        \App\Services\ActivityLogger::log('user.login', ['user_id' => $user->user_id]);

        $redirectRoute = $user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard';
        
        return redirect()->route($redirectRoute)->with('success', 'Login successful!');
    }

    /**
     * Resend OTP to the user stored in session (otp_user_id).
     */
    public function resendOtp(Request $request, SmsService $sms)
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $code = rand(100000, 999999);

        OtpCode::create([
            'user_id' => $user->user_id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $sendResult = null;
        if ($user->phone_number) {
            // Prefer sendOTP which uses the provider's OTP endpoint
            $sendResult = $sms->sendOTP($user->phone_number, "Your MediTrack OTP is: {$code}");
        }

        if (is_array($sendResult) && isset($sendResult['provider']) && $sendResult['provider'] === 'local-log') {
            session()->flash('debug_otp', $code);
        }

        return redirect()->route('auth.otp.form')->with('status', 'A new OTP has been sent to your phone.');
    }

    public function showRegister(){
         return view('auth.register'); 
        }

    public function register(Request $request){
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:1000',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:50',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'age' => $validated['age'] ?? null,
            'address'=> $validated['address'] ?? null,
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user'
        ]);
        
        // Activity log: user registration
        \App\Services\ActivityLogger::log('user.registered', ['user_id' => $user->user_id]);

        return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
    }

    public function logout(HttpRequest $request)
    {
        $userId = Auth::id();
        \App\Services\ActivityLogger::log('user.logout', ['user_id' => $userId]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Optionally delete all cookies - commented out so you can enable later.
        // $cookies = $request->cookies->all();
        // foreach ($cookies as $name => $value) {
        //     // Queue cookie deletion
        //     Cookie::queue(Cookie::forget($name));
        // }

        return redirect()->route('login');
    }
}
