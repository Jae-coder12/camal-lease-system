<?php
use App\Http\Controllers\{ActivityLogController, AdminSettingsController, ApplicationController, AuthController, BuildingController, DashboardController, LandingController, LeaseController, MyController, PaymentController, RegistrantController, StallController};
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/register', [LandingController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login')->middleware('guest');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit')->middleware('guest');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Any logged-in user
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leases/{lease}/document', [LeaseController::class, 'document'])->name('leases.document');
    Route::get('/leases/{lease}/download', [LeaseController::class, 'download'])->name('leases.download');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
});

Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', RoleMiddleware::class . ':admin'])->name('admin.dashboard');

// Stall owner / applicant
Route::middleware(['auth', RoleMiddleware::class . ':user'])->prefix('my')->name('my.')->group(function () {
    Route::get('/stalls', [MyController::class, 'stalls'])->name('stalls');
    Route::post('/stalls/{stall}/apply', [MyController::class, 'apply'])->name('apply');
    Route::get('/applications', [MyController::class, 'applications'])->name('applications');
    Route::get('/leases', [MyController::class, 'leases'])->name('leases');
    Route::post('/applications/{application}/cancel', [MyController::class, 'cancelApplication'])->name('applications.cancel');
    Route::put('/profile', [MyController::class, 'updateProfile'])->name('profile.update');
});

// Admin only
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
    Route::get('/admin/settings', [AdminSettingsController::class, 'edit'])->name('admin.settings');
    Route::put('/admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/registrants/{registrant}/profile', [RegistrantController::class, 'show'])->name('registrants.show');
    Route::resource('registrants', RegistrantController::class)->except('show');
    Route::resource('stalls', StallController::class)->except('show');
    Route::get('/buildings', [BuildingController::class, 'index'])->name('buildings.index');
    Route::post('/buildings', [BuildingController::class, 'store'])->name('buildings.store');
    Route::put('/buildings/{building}', [BuildingController::class, 'update'])->name('buildings.update');
    Route::delete('/buildings/{building}', [BuildingController::class, 'destroy'])->name('buildings.destroy');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/{application}/review', [ApplicationController::class, 'beginReview'])->name('applications.review');
    Route::post('/applications/{application}/approve', [ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/applications/{application}/complete', [ApplicationController::class, 'complete'])->name('applications.complete');
    Route::get('/applications/{application}/document', [ApplicationController::class, 'document'])->name('applications.document');
    Route::get('/leases', [LeaseController::class, 'index'])->name('leases.index');
    Route::post('/leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');
    Route::post('/leases/{lease}/renew', [LeaseController::class, 'renew'])->name('leases.renew');
    Route::put('/leases/{lease}', [LeaseController::class, 'update'])->name('leases.update');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/review', [PaymentController::class, 'review'])->name('payments.review');
});
