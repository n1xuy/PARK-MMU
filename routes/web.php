<?php

use App\Models\Announcement;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\ReportDataController;
use App\Http\Controllers\ParkingZoneController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ParkManageController;

// ============================================
// PUBLIC ROUTES
// ============================================

Route::get('/', function () {
    $announcement = Announcement::latest()->first();
    
    // Preload zones with their reports for today
    $zones = \App\Models\ParkingZone::with(['reports' => function($query) {
        $query->whereDate('created_at', today());
    }])->orderBy('zone_number')->get();
    
    // Precalculate statuses
    $controller = app(\App\Http\Controllers\ParkingZoneController::class);
    $zones->each(function ($zone) use ($controller) {
        $zone->calculated_status = $controller->calculateReliableStatus($zone);
        $zone->status_label = $controller->getStatusLabel($zone->calculated_status);
    });

    return view('home', compact('announcement', 'zones'));
})->name('home');

// Student Authentication Routes
Route::get('/student-login', function () {
    return view('studentlogin');
})->name('student.login');

Route::post('/student-login', [UserController::class, 'login'])->name('userlogin');

Route::get('/student-register', function() {
    return view('studentregister');
})->name('student.register');

Route::post('/student-register', [UserController::class, 'registration'])->name('registration');

// Admin Authentication Routes
Route::post('/admin-login', [AdminController::class, 'login'])->name('adminlogin');

// Logout Route
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Public Parking Information Routes
Route::get('/parking-detail/{zoneNumber}', [ParkingZoneController::class, 'show'])->name('parking.detail');
Route::get('/zone-stats', [ParkingZoneController::class, 'getZoneStats'])->name('zone.stats');
Route::get('/zone-statuses', [ParkingZoneController::class, 'getAllZoneStatuses']);
Route::get('/zones-for-map', [ParkingZoneController::class, 'getZonesForMap']);

// API Route for announcements
Route::get('/announcements', function () {
    return \App\Models\Announcement::all();
});

// Auto unblock route (can be called by cron jobs)
Route::post('/auto-unblock-expired', [ParkManageController::class, 'triggerAutoUnblock'])->name('auto-unblock-expired');

// ============================================
// AUTHENTICATED USER ROUTES
// ============================================

Route::middleware(['auth'])->group(function () {
    Route::post('/submit-report', [ReportController::class, 'submitReport']);
    Route::delete('/report/delete/{zoneNumber}', [ReportController::class, 'deleteReport'])->name('report.delete'); 
    Route::get('/check-report/{zoneNumber}', [ReportController::class, 'checkUserReport'])->name('report.check');
    Route::put('/update-report', [ReportController::class, 'updateReport'])->name('update-report');
    Route::post('/report-status', [ReportController::class, 'submitReport'])->name('report.submit');
});

// ============================================
// ADMIN ROUTES (NO MIDDLEWARE PROTECTION)
// ============================================

// Admin Dashboard
Route::get('/admin', function(){
    $announcement = Announcement::latest()->first(); 
    return view('admin', compact('announcement'));
})->name('admin.menu');

// Admin Password Management
Route::get('/admin/change-password', [AdminController::class, 'showChangePasswordForm'])->name('admin.changepw');
Route::post('/admin/change-password', [AdminController::class, 'updatePassword'])->name('admin.pwupdate');

// System Logs
Route::get('/admin-logs', [SystemLogController::class, 'index'])->name('admin.syslogs');

// Report Data
Route::get('/admin-reportdata', [ReportDataController::class, 'index'])->name('admin.report');

// Admin Announcements
Route::get('/admin/announcement', [AnnouncementController::class, 'handleAnnouncement'])->name('admin.announce');
Route::post('/admin/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
Route::put('/admin/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
Route::post('/admin/announcements/{announcement}/clear', [AnnouncementController::class, 'clear'])->name('announcements.clear');

// Zone Management
Route::get('/admin/zone-blocks', [ParkManageController::class, 'index'])->name('zoneblocks.index');
Route::post('/admin/zone-blocks/store', [ParkManageController::class, 'store'])->name('admin.parking-blocks.store');
Route::post('/admin/zone-blocks/store-recurring', [ParkManageController::class, 'storeRecurring']);
Route::post('/admin/zone-blocks/unblock/{zone}', [ParkManageController::class, 'unblock'])->name('zoneblocks.unblock');
Route::post('/admin/auto-unblock', [ParkManageController::class, 'triggerAutoUnblock'])->name('admin.auto-unblock');

// Zone Status & Statistics
Route::get('/admin/zone-statuses', [ParkingZoneController::class, 'getZoneStatus']);
Route::get('/admin/future-blocks', [ParkManageController::class, 'getFutureBlocks'])->name('admin.future-blocks');
Route::get('/admin/block-history', [ParkManageController::class, 'getBlockHistory'])->name('admin.block-history');
Route::get('/admin/block-stats', [ParkManageController::class, 'getBlockStats']);

// Test route
Route::get('/admin-test', function() {
    return 'Admin routes are working without middleware!';
});