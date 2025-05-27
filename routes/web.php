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

Route::get('/', function () {
    $announcement = \App\Models\Announcement::latest()->first(); 
    return view('home',compact('announcement'));
})->name('home');

Route::get('/student-login', function () {
    return view('studentlogin');
})->name('student.login');

Route::post('/student-login',[UserController::class,'login'])->name('userlogin');

Route::get('/student-register', function() {
    return view('studentregister');
})->name('student.register');

Route::post('/student-register',[UserController::class, 'registration'])->name('registration');

Route::get('/admin-login', function(){
    return view('adminlogin');
})->name('admin.login');
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::post('/admin-login',[AdminController::class, 'login'])->name('adminlogin');

Route::middleware(['auth:admin'])->group(function(){
Route::get('/admin/change-password',[AdminController::class,'AdminController@showChangePasswordForm'])->name('admin.changepw');
Route::post('/admin/change-password', [AdminController::class, 'updatePassword'])->name('admin.pwupdate');
});

Route::get('/admin', function(){
    $announcement = Announcement::latest()->first(); 
    return view('admin', compact('announcement'));
})->name('admin.menu');

Route::get('/admin-announce', function () {
    $announcements = Announcement::latest()->first() ?? new Announcement();
    return view('announcement-edit', compact('announcements'));
});

Route::get('/admin-parkmanage', function(){
    return view('parkmanagement');
})-> name('admin.parkmanage');

Route::get('/admin-logs', function(){
    return view('systemlogs');
})-> name('admin.syslogs');

Route::get('/admin-changepassword', function(){
    return view('changepassword');
})-> name('admin.changepw');

Route::get('/parkinginfo', function(){
    return view('parkingdetail');
})-> name('parkinfo');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

//report function route
Route::get('/zone-stats', [ParkingZoneController::class, 'getZoneStats']);
Route::post('/report-status', [ReportController::class, 'reportStatus'])
    ->middleware('auth')
    ->name('report.submit');

Route::get('/parking-detail/{zone_number}', [ParkingZoneController::class, 'show'])
     ->name('parkinfo');

Route::get('/admin-logs', [SystemLogController::class, 'index'])->name('admin.syslogs');

Route::get('/announcements', function () {
    return \App\Models\Announcement::all();
});

Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');

Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');

Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');

Route::post('/announcements/clear/{id}', [AnnouncementController::class, 'clear'])->name('announcements.clear');

Route::get('/admin-reportdata', [ReportDataController::class, 'index'])->name('admin.report');

Route::get('/admin-announce', [AnnouncementController::class, 'createOrEdit'])->name('admin.announce');

Route::get('/admin-announce', [AnnouncementController::class, 'create'])->name('admin.announce');



