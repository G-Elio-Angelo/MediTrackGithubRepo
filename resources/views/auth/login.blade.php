@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">

<div class="auth-container">

    <div class="login-card">

        <div class="logo-wrapper">
        </div>

        <h3 class="mb-3 text-center health-title">Login</h3>

        @if($errors->any())
            <div class="alert alert-danger-custom">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label class="fw-bold"><i class="fas fa-envelope me-2"></i>Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label class="fw-bold"><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" name="password"
                       class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg"
                        style="background-color:#1d3557; border:none;">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('register') }}" class="text-decoration-none">
                Already have an account? <strong>Register</strong>
            </a>
        </div>

    </div>

</div>
@endsection
