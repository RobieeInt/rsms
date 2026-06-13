<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\QuotationApprovalController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public quotation approval
Route::get('/quotation/approve/{token}', [QuotationApprovalController::class, 'show'])->name('quotation.approve');
Route::post('/quotation/approve/{token}', [QuotationApprovalController::class, 'process'])->name('quotation.approve.post');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clients
    Route::get('/clients', \App\Livewire\Clients\ClientList::class)->name('clients.index');
    Route::get('/clients/create', \App\Livewire\Clients\ClientForm::class)->name('clients.create');
    Route::get('/clients/{client}/edit', \App\Livewire\Clients\ClientForm::class)->name('clients.edit');
    Route::get('/clients/{client}', \App\Livewire\Clients\ClientShow::class)->name('clients.show');

    // Technicians
    Route::get('/technicians', \App\Livewire\Technicians\TechnicianList::class)->name('technicians.index');
    Route::get('/technicians/create', \App\Livewire\Technicians\TechnicianForm::class)->name('technicians.create');
    Route::get('/technicians/{user}/edit', \App\Livewire\Technicians\TechnicianForm::class)->name('technicians.edit');

    // Assets
    Route::get('/assets', \App\Livewire\Assets\AssetList::class)->name('assets.index');
    Route::get('/assets/create', \App\Livewire\Assets\AssetForm::class)->name('assets.create');
    Route::get('/clients/{client}/assets/create', \App\Livewire\Assets\AssetForm::class)->name('clients.assets.create');
    Route::get('/assets/{asset}/edit', \App\Livewire\Assets\AssetForm::class)->name('assets.edit');
    Route::get('/assets/{asset}', \App\Livewire\Assets\AssetShow::class)->name('assets.show');

    // Schedules
    Route::get('/schedules', \App\Livewire\Schedules\ScheduleList::class)->name('schedules.index');
    Route::get('/schedules/create', \App\Livewire\Schedules\ScheduleForm::class)->name('schedules.create');
    Route::get('/schedules/{schedule}', \App\Livewire\Schedules\ScheduleShow::class)->name('schedules.show');
    Route::get('/schedules/{schedule}/edit', \App\Livewire\Schedules\ScheduleForm::class)->name('schedules.edit');

    // Visit Reports
    Route::get('/reports', \App\Livewire\Reports\ReportList::class)->name('reports.index');
    Route::get('/reports/create/{schedule}', \App\Livewire\Reports\ReportForm::class)->name('reports.create');
    Route::get('/reports/{report}', \App\Livewire\Reports\ReportShow::class)->name('reports.show');
    Route::get('/reports/{report}/edit', \App\Livewire\Reports\ReportForm::class)->name('reports.edit');

    // Findings
    Route::get('/findings', \App\Livewire\Findings\FindingList::class)->name('findings.index');
    Route::get('/findings/create', \App\Livewire\Findings\FindingForm::class)->name('findings.create');
    Route::get('/findings/{finding}', \App\Livewire\Findings\FindingShow::class)->name('findings.show');
    Route::get('/findings/{finding}/edit', \App\Livewire\Findings\FindingForm::class)->name('findings.edit');

    // Quotations
    Route::get('/quotations', \App\Livewire\Quotations\QuotationList::class)->name('quotations.index');
    Route::get('/quotations/create', \App\Livewire\Quotations\QuotationForm::class)->name('quotations.create');
    Route::get('/quotations/{quotation}', \App\Livewire\Quotations\QuotationShow::class)->name('quotations.show');
    Route::get('/quotations/{quotation}/edit', \App\Livewire\Quotations\QuotationForm::class)->name('quotations.edit');

    // Invoices
    Route::get('/invoices', \App\Livewire\Invoices\InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', \App\Livewire\Invoices\InvoiceForm::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', \App\Livewire\Invoices\InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', \App\Livewire\Invoices\InvoiceForm::class)->name('invoices.edit');

    // PDF Downloads
    Route::get('/pdf/report/{report}', [PdfController::class, 'report'])->name('pdf.report');
    Route::get('/pdf/quotation/{quotation}', [PdfController::class, 'quotation'])->name('pdf.quotation');
    Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'invoice'])->name('pdf.invoice');

    // Settings
    Route::get('/settings', \App\Livewire\Settings\CompanySettings::class)->name('settings.index');

    // Profile
    Route::get('/profile', \App\Livewire\Profile\UserProfile::class)->name('profile');

    // Notifications
    Route::get('/notifications', \App\Livewire\Notifications\NotificationCenter::class)->name('notifications.index');
});
