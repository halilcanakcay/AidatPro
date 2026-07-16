<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DueController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\OwnerReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/rapor/{token}', [OwnerReportController::class, 'show'])->name('owner.reports.show');

Route::get('/downloads', [DownloadController::class, 'show'])->name('downloads.show');
Route::post('/downloads', [DownloadController::class, 'download'])
    ->middleware('throttle:6,1')
    ->name('downloads.download');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('sites', SiteController::class)->except(['show']);
    Route::resource('apartments', ApartmentController::class)->except(['show']);
    Route::resource('residents', ResidentController::class)->except(['show']);
    Route::resource('dues', DueController::class)->except(['show']);
    Route::resource('payments', PaymentController::class)->except(['show']);
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/{payment}/receipt/pdf', [PaymentController::class, 'receiptPdf'])->name('payments.receipt.pdf');
    Route::resource('expenses', ExpenseController::class)->except(['show']);
    Route::resource('announcements', AnnouncementController::class)->except(['show']);
    Route::get('/announcements/{announcement}/send', [AnnouncementController::class, 'send'])->name('announcements.send');
    Route::post('/announcements/{announcement}/mail', [AnnouncementController::class, 'sendMail'])->name('announcements.mail');
    Route::resource('authorities', AuthorityController::class)->except(['show']);
    Route::post('/owner-report-links', [AuthorityController::class, 'storeOwnerLink'])->name('owner-report-links.store');
    Route::delete('/owner-report-links/{ownerReportLink}', [AuthorityController::class, 'destroyOwnerLink'])->name('owner-report-links.destroy');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/telegram', [SettingsController::class, 'updateTelegram'])->name('settings.telegram');
    Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::get('/integrations/vakifbank', [IntegrationController::class, 'vakifbank'])->name('integrations.vakifbank');
    Route::post('/integrations/vakifbank/sync', [IntegrationController::class, 'syncVakifbank'])->name('integrations.vakifbank.sync');
    Route::post('/integrations/vakifbank/settings', [IntegrationController::class, 'updateVakifbankSettings'])->name('integrations.vakifbank.settings');
    Route::post('/integrations/vakifbank/transactions/{bankTransaction}/approve', [IntegrationController::class, 'approveVakifbankTransaction'])->name('integrations.vakifbank.transactions.approve');
});
