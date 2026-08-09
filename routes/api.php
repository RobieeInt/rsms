<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FindingController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuotationApprovalController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TechnicianController;
use Illuminate\Support\Facades\Route;

// All API route names are prefixed with "api." so they never collide with
// routes/web.php's identically-named routes (e.g. "clients.index" exists in
// both files — without this prefix, whichever loads last wins the name
// lookup and route('clients.index') in the web app starts pointing at /api/*).
Route::name('api.')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/quotation/approve/{token}', [QuotationApprovalController::class, 'process'])->middleware('throttle:5,1');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Push notification device tokens
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);

        // Clients
        Route::apiResource('clients', ClientController::class);
        Route::post('/clients/{client}/generate-retainer', [ClientController::class, 'generateRetainer']);

        // Technicians — account management is admin-only (creating/editing
        // includes setting another user's password, so this must never be
        // reachable by a plain technician).
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('technicians', TechnicianController::class);
        });

        // Assets
        Route::apiResource('assets', AssetController::class);

        // Schedules
        Route::apiResource('schedules', ScheduleController::class);
        Route::post('/schedules/{schedule}/checkin', [ScheduleController::class, 'checkIn']);
        Route::post('/schedules/{schedule}/checkout', [ScheduleController::class, 'checkOut']);
        Route::post('/schedules/{schedule}/cancel', [ScheduleController::class, 'cancel']);

        // Reports
        Route::apiResource('reports', ReportController::class);
        Route::post('/reports/{report}/send-email', [ReportController::class, 'sendEmail']);

        // Findings
        Route::apiResource('findings', FindingController::class);
        Route::post('/findings/{finding}/recommendations', [FindingController::class, 'addRecommendation']);
        Route::put('/findings/{finding}/status', [FindingController::class, 'updateStatus']);

        // Quotations
        Route::apiResource('quotations', QuotationController::class);
        Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send']);
        Route::post('/quotations/{quotation}/convert-to-invoice', [QuotationController::class, 'convertToInvoice']);

        // Invoices
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
        Route::post('/invoices/{invoice}/resend-email', [InvoiceController::class, 'resendEmail']);
        Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);
        Route::post('/invoices/generate-retainer', [InvoiceController::class, 'generateRetainer']);

        // Settings
        Route::get('/settings', [SettingController::class, 'show']);
        Route::put('/settings', [SettingController::class, 'update']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        // PDF Downloads
        Route::get('/pdf/report/{report}', [PdfController::class, 'report']);
        Route::get('/pdf/quotation/{quotation}', [PdfController::class, 'quotation']);
        Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'invoice']);
    });
});
