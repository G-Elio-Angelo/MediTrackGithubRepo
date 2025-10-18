@extends('layouts.app')


@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh">
  <div class="col-md-6">
    <div class="card shadow p-4">
      <h3 class="mb-3 text-center">Login to MediTrack</h3>

      @if($errors->any())
        <div class="alert alert-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>

      <div class="mt-3 text-center">
        <a href="{{ route('register') }}">Don't have an account? Register</a>
      </div>
    </div>
  </div>
</div>
@endsection

