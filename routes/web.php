<?php

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

Route::middleware(['auth', 'active', 'verified', 'no-cache'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('appraisals')->name('appraisals.')->group(function () {
        Route::get('/', [AppraisalController::class, 'index'])->name('index');
        Route::get('/{appraisal}', [AppraisalController::class, 'show'])->name('show');
        Route::get('/{appraisal}/edit', [AppraisalController::class, 'edit'])->name('edit');
        Route::put('/{appraisal}', [AppraisalController::class, 'update'])->name('update');
        Route::get('/{appraisal}/review', [AppraisalController::class, 'review'])->name('review');
        Route::put('/{appraisal}/review', [AppraisalController::class, 'updateReview'])->name('review.update');
        Route::get('/{appraisal}/sign', [AppraisalController::class, 'signOff'])->name('sign');
        Route::post('/{appraisal}/sign', [AppraisalController::class, 'submitSignOff'])->name('sign.submit');
        Route::get('/{appraisal}/sign-in-person', [AppraisalController::class, 'signInPerson'])->name('sign-in-person');
        Route::post('/{appraisal}/sign-in-person', [AppraisalController::class, 'submitSignInPerson'])->name('sign-in-person.submit');
        Route::get('/{appraisal}/pdf', [AppraisalController::class, 'downloadPdf'])->name('pdf');
    });

    Route::prefix('reports')->name('reports.')->middleware('role:admin,reviewer')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('pd-hours', [ReportController::class, 'pdHours'])->name('pd-hours');
        Route::get('pd-hours/export', [ReportController::class, 'exportPdHours'])->name('pd-hours.export');

        Route::middleware('role:admin')->group(function () {
            Route::get('completion', [ReportController::class, 'completion'])->name('completion');
            Route::get('completion/export', [ReportController::class, 'exportCompletion'])->name('completion.export');
            Route::get('ratings', [ReportController::class, 'ratings'])->name('ratings');
            Route::get('ratings/export', [ReportController::class, 'exportRatings'])->name('ratings.export');
            Route::get('audit', [ReportController::class, 'audit'])->name('audit');
            Route::get('audit/export', [ReportController::class, 'exportAudit'])->name('audit.export');
        });
    });

    // Creating and opening appraisals is shared with reviewers, who are limited to their own staff.
    Route::prefix('admin')->name('admin.')->middleware('role:admin,reviewer')->group(function () {
        Route::get('appraisals/create', [AdminAppraisalController::class, 'create'])->name('appraisals.create');
        Route::post('appraisals', [AdminAppraisalController::class, 'store'])->name('appraisals.store');
        Route::get('appraisals/prior-targets/{user}', [AdminAppraisalController::class, 'priorTargets'])->name('appraisals.prior-targets');
        Route::patch('appraisals/{appraisal}/open', [AdminAppraisalController::class, 'open'])->name('appraisals.open');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('users/import', [AdminUserController::class, 'showImport'])->name('users.import');
        Route::post('users/import', [AdminUserController::class, 'import'])->name('users.import.submit');
        Route::get('users/import/template', [AdminUserController::class, 'downloadTemplate'])->name('users.import.template');
        Route::resource('users', AdminUserController::class)->except(['show']);

        Route::get('appraisals', [AdminAppraisalController::class, 'index'])->name('appraisals.index');
        Route::get('appraisals/export', [AdminAppraisalController::class, 'exportCsv'])->name('appraisals.export');
        Route::get('appraisals/{appraisal}/edit', [AdminAppraisalController::class, 'edit'])->name('appraisals.edit');
        Route::put('appraisals/{appraisal}', [AdminAppraisalController::class, 'update'])->name('appraisals.update');
        Route::delete('appraisals/{appraisal}', [AdminAppraisalController::class, 'destroy'])->name('appraisals.destroy');
        Route::get('appraisals/{appraisal}/reopen', [AdminAppraisalController::class, 'showReopen'])->name('appraisals.reopen');
        Route::patch('appraisals/{appraisal}/reopen', [AdminAppraisalController::class, 'reopen'])->name('appraisals.reopen.submit');
    });
});

require __DIR__.'/auth.php';
