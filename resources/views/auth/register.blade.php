@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">

<div class="glass-container d-flex justify-content-center align-items-center">
    <div class="card shadow p-4 login-card" style="max-width: 500px; width: 100%;">

        <!-- Logo -->
        <div class="text-center mb-2">
         
        </div>

        <h3 class="mb-3 text-center health-title">Create Your Account</h3>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Registration Failed:</strong>
                <ul class="mt-2 mb-0 ps-3">
                    @foreach($errors->all() as $error)
                       <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="fw-bold">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" 
                           class="form-control" placeholder="Enter Username" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" 
                           class="form-control" placeholder="First Name"  required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" 
                           class="form-control" placeholder="Middle Name">
                </div>

                <div class="col-12 mb-3">
                    <label class="fw-bold">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" 
                           class="form-control" placeholder="Last Name" required>
                </div>

                <div class="col-12 mb-3">
                    <label class="fw-bold">Age</label>
                    <input type="number" name="age" value="{{ old('age') }}" 
                           class="form-control" placeholder="Age" >
                </div>

                <div class="col-12 mb-3">
                    <label class="fw-bold">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" 
                           class="form-control"placeholder="Address"  >
                </div>

                <div class="col-12 mb-3">
                    <label class="fw-bold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                    class="form-control" placeholder="Email" required>
                </div>

                <div class="col-12 mb-3">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" 
                           class="form-control" placeholder="Phone Number">
                </div>

                <div class="col-12 mb-4">
                    <label class="fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg"
                        style="background-color:#1d3557; border:none;">
                    Register
                </button>
            </div>

        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('login') }}" class="text-decoration-none">
                Already have an account? <strong>Login</strong>
            </a>
        </div>

    </div>
</div>
@endsection
