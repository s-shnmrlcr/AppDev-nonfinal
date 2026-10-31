<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\PayoutReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    Route::get('/beneficiaries/create', [BeneficiaryController::class, 'create'])->name('beneficiaries.create');
    Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');
    Route::post('/beneficiaries/import', [BeneficiaryController::class, 'import'])->name('beneficiaries.import');
    Route::get('/beneficiaries/export', [BeneficiaryController::class, 'export'])->name('beneficiaries.export');

    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification/review', [VerificationController::class, 'review'])->name('verification.review');

    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [ProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');

    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/export', [PayoutController::class, 'export'])->name('payouts.export');

    Route::get('/payouts/{payout}/releases', [PayoutReleaseController::class, 'index'])->name('payout-releases.index');
    Route::post('/payouts/{payout}/releases', [PayoutReleaseController::class, 'store'])->name('payout-releases.store');
    Route::patch('/payout-releases/{release}/status', [PayoutReleaseController::class, 'updateStatus'])->name('payout-releases.update-status');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/audit-logs', [AuditController::class, 'index'])->name('audit-logs.index');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});
