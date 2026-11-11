<?php
use App\Http\Controllers\{ApplicationController, AuthController, DashboardController, LandingController, LeaseController, MyController, RegistrantController, StallController};
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/register', [LandingController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Any logged-in user
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leases/{lease}/document', [LeaseController::class, 'document'])->name('leases.document');
    Route::get('/leases/{lease}/download', [LeaseController::class, 'download'])->name('leases.download');
});

// Stall owner / applicant
Route::middleware(['auth', RoleMiddleware::class . ':user'])->prefix('my')->name('my.')->group(function () {
    Route::get('/stalls', [MyController::class, 'stalls'])->name('stalls');
    Route::post('/stalls/{stall}/apply', [MyController::class, 'apply'])->name('apply');
    Route::get('/applications', [MyController::class, 'applications'])->name('applications');
    Route::get('/leases', [MyController::class, 'leases'])->name('leases');
});

// Admin only
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::resource('registrants', RegistrantController::class)->except('show');
    Route::resource('stalls', StallController::class)->except('show');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/{application}/approve', [ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject'])->name('applications.reject');
    Route::get('/leases', [LeaseController::class, 'index'])->name('leases.index');
    Route::post('/leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');
});
