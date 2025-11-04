@extends('layouts.app')

@section('content')
<div class="glass-container d-flex justify-content-center align-items-center" style="min-height:80vh">
  <div class="card shadow p-4 login-card">
      <h3 class="mb-3 text-center">Register</h3>
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required value="{{ old('username') }}"></div>
        <div class="row">
          <div class="col-md-4 mb-3"><label>First name</label>
          <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}"></div>
          <div class="col-md-4 mb-3"><label>Middle name</label>
          <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}"></div>
          <div class="col-md-4 mb-3"><label>Last name</label>
          <input type="text" name="last_name" class="form-control" required value="{{ old('last_name') }}"></div>
        </div>
        <div class="mb-3"><label>Age</label>
        <input type="number" name="age" class="form-control" value="{{ old('age') }}"></div>
        <div class="mb-3"><label>Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address') }}"></div>
        <div class="mb-3"><label>Email</label>
        <input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
        <div class="mb-3"><label>Phone</label>
        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}"></div>
        <div class="mb-3"><label>Password</label>
        <input type="password" name="password" class="form-control" required></div>
        <div class="d-grid gap-2"><button type="submit" class="btn btn-success">Register</button></div>
      </form>
      <div class="mt-3 text-center"><a href="{{ route('login') }}">Already have an account?</a></div>
    </div>
</div>
@endsection
