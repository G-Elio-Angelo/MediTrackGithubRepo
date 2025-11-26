@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/custom-otp.css') }}"> 

<div class="container d-flex justify-content-center align-items-center auth-page-bg">
    <div class="col-lg-5 col-md-7 col-sm-10">
        <div class="card shadow-lg p-4 otp-card"> 
            
            <div class="text-center mb-4">
                <i class="fas fa-mobile-alt fa-3x text-primary mb-2"></i>
                <h3 class="fw-bold mb-0">OTP Verification</h3>
                <p class="text-muted mt-2">A 6-digit code has been sent to your registered phone number.</p>
            </div>

            {{-- Success or status messages --}}
            @if(session('status'))
                <div class="alert alert-success text-center" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error messages --}}
            @if($errors->any())
                <div class="alert alert-danger text-center" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.otp.verify') }}">
                @csrf
                <div class="mb-4">
                    <label for="otp" class="form-label text-muted d-block text-center fw-bold">Enter Code Below</label>
                    <input type="text" name="otp" id="otp" maxlength="6" 
                           class="form-control text-center fs-3 otp-input" 
                           placeholder="------" required pattern="\d{6}">
                    <small class="text-muted d-block text-center mt-2">
                        OTP expires in 5 minutes. <a href="#" class="resend-link">Resend Code</a>
                    </small>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg verify-btn">Verify</button> 
                </div>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-chevron-left me-1"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection