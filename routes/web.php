<?php

use App\Http\Controllers\Admin\AppraisalCycleController;
use App\Http\Controllers\Admin\AppraisalController as AdminAppraisalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('appraisals')->name('appraisals.')->group(function () {
        Route::get('/', [AppraisalController::class, 'index'])->name('index');
        Route::get('/{appraisal}', [AppraisalController::class, 'show'])->name('show');
        Route::get('/{appraisal}/edit', [AppraisalController::class, 'edit'])->name('edit');
        Route::put('/{appraisal}', [AppraisalController::class, 'update'])->name('update');
        Route::get('/{appraisal}/review', [AppraisalController::class, 'review'])->name('review');
        Route::put('/{appraisal}/review', [AppraisalController::class, 'updateReview'])->name('review.update');
        Route::get('/{appraisal}/sign', [AppraisalController::class, 'signOff'])->name('sign');
        Route::post('/{appraisal}/sign', [AppraisalController::class, 'submitSignOff'])->name('sign.submit');
        Route::get('/{appraisal}/pdf', [AppraisalController::class, 'downloadPdf'])->name('pdf');
    });

    Route::prefix('reports')->name('reports.')->middleware('role:admin,reviewer')->group(function () {
        Route::get('pd-hours', [ReportController::class, 'pdHours'])->name('pd-hours');
        Route::get('pd-hours/export', [ReportController::class, 'exportPdHours'])->name('pd-hours.export');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::resource('cycles', AppraisalCycleController::class)->except(['show']);

        Route::get('appraisals', [AdminAppraisalController::class, 'index'])->name('appraisals.index');
        Route::get('appraisals/export', [AdminAppraisalController::class, 'exportCsv'])->name('appraisals.export');
        Route::get('appraisals/create', [AdminAppraisalController::class, 'create'])->name('appraisals.create');
        Route::post('appraisals', [AdminAppraisalController::class, 'store'])->name('appraisals.store');
        Route::patch('appraisals/{appraisal}/open', [AdminAppraisalController::class, 'open'])->name('appraisals.open');
        Route::get('appraisals/prior-targets/{user}', [AdminAppraisalController::class, 'priorTargets'])->name('appraisals.prior-targets');
    });
});

require __DIR__.'/auth.php';
