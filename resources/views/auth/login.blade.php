@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">

<div class="glass-container">

    <div class="login-card">

        <div class="logo-wrapper">
            <img src="{{ asset('images/meditrack-logo.png') }}" alt="MediTrack" class="meditrack-logo">
        </div>

        <h3 class="mb-3 text-center health-title">MediTrack System Login</h3>
        <p class="subtitle text-center">Your personal medicine tracker</p>

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
                <button type="submit" class="btn btn-primary btn-lg login-btn">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('register') }}" class="text-decoration-none">
                Doesn't have an Account? <strong>Register</strong>
            </a>
        </div>

    </div>

</div>
@endsection
