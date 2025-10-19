@extends('layouts.app')
@section('content')
<div class="container py-4">
  <h2>Welcome, {{ auth()->user()->username }}</h2>
  <p>Available Medicines:</p>
  <ul>
  @foreach($medicines as $m)
    <li>{{ $m->medicine_name }} — {{ $m->stock }} pcs</li>
  @endforeach
  </ul>
</div>
@endsection
