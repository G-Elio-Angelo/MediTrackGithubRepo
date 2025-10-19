<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AdminController;

Route::get('/', fn() => redirect()->route('login'));

// --- AUTH ROUTES ---
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'loginStep'])->name('login.post');
Route::get('otp', [AuthController::class, 'showOtpForm'])->name('auth.otp.form');
Route::post('otp', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

// --- ADMIN ROUTES ---
Route::middleware(['admin'])->group(function () {
    Route::get('admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('admin/medicines', [AdminController::class, 'medicines'])->name('admin.medicines');
    Route::delete('admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::delete('admin/medicines/{id}', [AdminController::class, 'deleteMedicine'])->name('admin.medicines.delete');
});

// --- USER ROUTES ---
Route::middleware(['auth'])->group(function () {
    Route::get('user/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('medicines', MedicineController::class);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
