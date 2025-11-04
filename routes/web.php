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
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Correct update route will be defined below (avoid duplicate admin prefix)
    Route::get('/dashboard', [AdminController::class, 'admindashb'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    
    Route::get('/medicines', [AdminController::class, 'medicines'])->name('admin.medicines');
    Route::post('/medicines', [AdminController::class, 'storeMedicine'])->name('admin.medicines.store');
    Route::put('/medicines/{id}', [AdminController::class, 'updateMedicine'])->name('admin.medicines.update');
    Route::delete('/medicines/{id}', [AdminController::class, 'deleteMedicine'])->name('admin.medicines.delete');

    Route::get('/test-admin', function() {
        return 'Admin middleware works!';
    })->middleware('admin');
});

// --- USER ROUTES ---
Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::post('/intake/{id}/confirm', [DashboardController::class, 'confirmIntake'])->name('user.intake.confirm');
    Route::resource('medicines', MedicineController::class);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
