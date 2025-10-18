@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh">
  <div class="col-md-6">
    <div class="card shadow p-4">
      <h3 class="mb-3 text-center">Register</h3>
      <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Phone</label><input type="text" name="phone_number" class="form-control"></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="d-grid gap-2"><button type="submit" class="btn btn-success">Register</button></div>
      </form>
      <div class="mt-3 text-center"><a href="{{ route('login') }}">Already have an account?</a></div>
    </div>
  </div>
</div>
@endsection
