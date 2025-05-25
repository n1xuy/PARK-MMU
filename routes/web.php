<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ParkingZoneController;
use App\Http\Controllers\AnnouncementController;

Route::get('/', function () {
    $announcement = App\Models\Announcement::active()->latest()->first();
    return view('home',['announcement' => $announcement]);;
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

Route::post('/admin-login',[AdminController::class, 'login'])->name('adminlogin');

Route::get('/admin', function(){
    return view('admin');
})->name('admin.menu');

Route::get('/admin-announce', function(){
    return view('announcementedit');
})->name('admin.announce');

Route::get('/admin-parkmanage', function(){
    return view('parkmanagement');
})-> name('admin.parkmanage');

Route::get('/admin-reportdata', function(){
    return view('reportdata');
})-> name('admin.report');

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

Route::post('/admin/announcement/update', [AnnouncementController::class, 'update'])->name('announcement.update');

