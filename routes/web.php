<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ApproverAssignmentController;
use App\Http\Controllers\SignatureStyleController;
use App\Http\Controllers\AdminController; // Import AdminController
use Illuminate\Support\Facades\Log;

// Include debug routes in development environment only
if (app()->environment('local')) {
    include __DIR__ . '/debug-routes.php';
    include __DIR__ . '/debug-department-routes.php';
}

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Redirect /login to root URL
Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Unified Requests
    Route::get('/requests', [RequestController::class, 'index'])->name('request.index'); // For listing all types of requests
    Route::get('/requests/create', [RequestController::class, 'create'])->name('request.create'); // Unified create form
    Route::post('/requests/submit-for-confirmation', [RequestController::class, 'submitForConfirmation'])->name('request.submit_for_confirmation'); // New: initial submission for confirmation
    Route::get('/requests/confirmation', [RequestController::class, 'showConfirmationPage'])->name('request.show_confirmation_page'); // New: display confirmation page
    Route::get('/requests/edit-before-confirmation', [RequestController::class, 'editBeforeConfirmation'])->name('request.edit_before_confirmation'); // New: for going back to edit from confirmation
    Route::post('/requests', [RequestController::class, 'store'])->name('request.store'); // Unified store action (now for final submission from confirmation)

    // Approvals Route (for users with 'Approver' accessRole)
    Route::get('/approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index')
        ->middleware('can:view-approvals'); // We will create this Gate

    Route::get('/approvals/{formRequest}/show', [ApprovalController::class, 'show'])
        ->name('approvals.show')
        ->middleware('can:view-approvals'); // Protects even seeing the detail page

    // Approval Actions
    Route::post('/approvals/{formRequest}/note', [ApprovalController::class, 'note'])
        ->name('approvals.note')
        ->middleware('can:view-approvals');

    Route::post('/approvals/{formRequest}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve')
        ->middleware('can:view-approvals');

    Route::post('/approvals/{formRequest}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject')
        ->middleware('can:view-approvals');

    // Approver Assignment Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/approver-assignments', [ApproverAssignmentController::class, 'index'])
            ->name('approver-assignments.index')
            ->middleware('can:manage-approvers');

        Route::put('/approver-assignments/{user}', [ApproverAssignmentController::class, 'update'])
            ->name('approver-assignments.update')
            ->middleware('can:manage-approvers');

        Route::get('/approver-assignments/check-updates', [ApproverAssignmentController::class, 'checkUpdates'])
            ->name('approver-assignments.check-updates')
            ->middleware('can:manage-approvers');
    });

    // Leave Requests -- REMOVED
    // Route::get('/requests/leave/create', [RequestController::class, 'createLeave'])->name('request.create.leave');
    // Route::post('/requests/leave', [RequestController::class, 'storeLeave'])->name('request.store.leave');

    Route::get('/request/{formId}/track', [RequestController::class, 'track'])
        ->name('request.track');

    // Signature Styles
    Route::get('/signature-styles', [SignatureStyleController::class, 'index'])->name('signature-styles.index');

    // Approvals routes
    Route::post('/approvals/batch', [ApprovalController::class, 'batch'])->name('approvals.batch');

    Route::get('/request/{formId}/print', [RequestController::class, 'printView'])->name('request.print');

    // Approvals routes
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/check-updates', [ApprovalController::class, 'checkUpdates'])->name('approval.check-updates');
    Route::get('/approval/{formId}', [ApprovalController::class, 'view'])->name('approval.view');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    // Add other admin routes here
    Route::get('/requests/{formId}/track', [AdminController::class, 'showRequestTrack'])->name('request.track');
});

Route::get('/test-log', function () {
    Log::info('This is a test log entry from /test-log route.');
    return 'Test log entry attempted. Check laravel.log.';
});

require __DIR__ . '/auth.php';
