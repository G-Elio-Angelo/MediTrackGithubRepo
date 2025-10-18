@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh">
  <div class="col-md-6">
    <div class="card shadow p-4">
      <h3 class="mb-3 text-center">OTP Verification</h3>

      {{-- Success or status messages --}}
      @if(session('status'))
        <div class="alert alert-info text-center">
          {{ session('status') }}
        </div>
      @endif

      {{-- Error messages --}}
      @if($errors->any())
        <div class="alert alert-danger text-center">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('auth.otp.verify') }}">
        @csrf
        <div class="mb-3">
          <label for="otp" class="form-label">Enter OTP Code</label>
          <input type="text" name="otp" id="otp" maxlength="6" class="form-control text-center fs-4" placeholder="123456" required>
          <small class="text-muted">Please check your SMS for the code. OTP expires in 5 minutes.</small>
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-success">Verify</button>
        </div>
      </form>

      <div class="mt-3 text-center">
        <a href="{{ route('login') }}">← Back to Login</a>
      </div>
    </div>
  </div>
</div>
@endsection
