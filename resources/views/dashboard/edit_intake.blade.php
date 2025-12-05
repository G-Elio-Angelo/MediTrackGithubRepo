@extends('layouts.app')

@section('content')
{{-- Include CSS and Font Awesome for consistent styling --}}
<link rel="stylesheet" href="{{ asset('css/meditrack-theme.css') }}">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" xintegrity="sha384-AYmEC3Yw5U5dD7i7t53X30m46O7hF6+I23g9M+B4hL2t8F9gYt5Tz30O+gR7/d6KxYj" crossorigin="anonymous">

<div class="mtk-app">
    <aside class="mtk-sidebar">
        <div class="mtk-brand">
            <h1 class="mtk-brand-title">MediTrack</h1>
        </div>

        <nav class="mtk-nav">
            <a href="{{ route('admin.dashboard') }}" class="mtk-nav-item">
                <i class="fal fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.medicines') }}" class="mtk-nav-item">
                <i class="fal fa-box-alt"></i><span>Inventory (Medicines)</span>
            </a>
            <a href="{{ route('admin.users') }}" class="mtk-nav-item">
                <i class="fal fa-users-crown"></i><span>Admin & User Records</span>
            </a>
            <a href="{{ route('admin.logs') }}" class="mtk-nav-item">
                <i class="fal fa-file-alt"></i><span>Activity Reports</span>
            </a>
            <a href="{{ route('admin.intakes') }}" class="mtk-nav-item">
                <i class="fal fa-pills"></i><span>Intake Records</span>
            </a>
        </nav>
        
        <div class="mtk-sidebar-bottom-actions">
            <form method="POST" action="{{ route('logout') }}" class="w-100 mb-2">
                @csrf
                <button type="submit" class="mtk-btn-secondary w-100">
                    <i class="fal fa-sign-out"></i> Logout
                </button>
            </form>
            
            <div class="mtk-sidebar-footer text-center">
                <small class="text-muted">v1.0 • MediTrack</small>
            </div>
        </div>

    </aside>

    <main class="mtk-main">
        <header class="mtk-topbar">
            <div class="mtk-search">
                <i class="fal fa-search mtk-search-icon"></i>
                <input type="text" placeholder="Search..." aria-label="Search">
            </div>

            <div class="mtk-top-right">
                @auth
                <div class="mtk-user">
                    <div class="mtk-avatar">EI</div>
                    <div class="mtk-user-meta">
                        <div class="mtk-user-name">{{ Auth::user()->first_name ?? 'Admin' }}</div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="link-logout" type="submit"><i class="fal fa-sign-out"></i> Logout</button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <section class="mtk-content">
            <div class="mtk-hero">
                <h2>Edit Medicine Intake <i class="fal fa-edit"></i></h2>
                <p class="muted">Update the details for this patient medicine log.</p>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h5 class="panel-title">Editing Record #{{ $intake->id }}</h5>
                    {{-- Updated button style to match dashboard --}}
                    <a href="{{ route('admin.intakes') }}" class="mtk-btn-secondary"><i class="fal fa-arrow-left"></i> Back to Intakes</a>
                </div>
                <div class="panel-body">

                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            <h6 class="mb-2"><i class="fal fa-exclamation-triangle"></i> Validation Errors:</h6>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.intakes.update', $intake->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Patient/User</label>
                            <select name="user_id" class="form-select" required>
                                @foreach($users as $u)
                                    <option value="{{ $u->user_id }}" {{ $u->user_id == $intake->user_id ? 'selected' : '' }}>
                                        {{ $u->full_name ?? $u->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Medicine</label>
                            <select name="medicine_id" class="form-select" required>
                                @foreach($medicines as $m)
                                    <option value="{{ $m->id }}" {{ $m->id == $intake->medicine_id ? 'selected' : '' }}>
                                        {{ $m->medicine_name }} (Batch: {{ $m->batch_number ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Intake Time</label>
                            <input type="datetime-local" name="intake_time" class="form-control" value="{{ $intake->intake_time ? $intake->intake_time->format('Y-m-d\TH:i') : '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Intake Interval (Minutes)</label>
                            <input type="number" name="intake_interval_minutes" class="form-control" value="{{ $intake->medicine->intake_interval_minutes ?? '--' }}" placeholder="Enter intake interval in minutes" disabled>
                            <small class="text-muted mt-1 d-block">This reflects the interval set on the selected medicine and cannot be changed here.</small>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="status" value="1" class="form-check-input" id="statusCheck" {{ $intake->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusCheck">
                                <i class="fal fa-check-circle"></i> Mark as taken
                            </label>
                        </div>

                        <button type="submit" class="mtk-btn"><i class="fal fa-save"></i> Save Changes</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection