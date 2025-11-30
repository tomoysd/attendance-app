<?php

use Illuminate\Support\Facades\Route;

// 一般ユーザー用コントローラ
use App\Http\Controllers\Auth\AttendanceController;
use App\Http\Controllers\Auth\StampCorrectionRequestController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// 管理者用コントローラ
use App\Http\Controllers\Admin\LoginController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;


/*
|--------------------------------------------------------------------------
| 一般ユーザー：認証不要
|--------------------------------------------------------------------------
*/

// 会員登録（GET / POST）
Route::get('/register', [RegisterController::class, 'create'])->name('register.show');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// ログイン（GET / POST）
Route::get('/login', [LoginController::class, 'create'])->name('login.show');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');


/*
|--------------------------------------------------------------------------
| 一般ユーザー：ログイン後のみ
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 出勤登録画面（勤怠登録）
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 修正申請一覧（一般）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('correction.index');
});


/*
|--------------------------------------------------------------------------
| 管理者：認証不要
|--------------------------------------------------------------------------
*/
// 管理者ログイン
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login.show');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.store');


/*
|--------------------------------------------------------------------------
| 管理者：ログイン後のみ
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('admin')->group(function () {

    // 勤怠一覧（管理者）
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.index');

    // 勤怠詳細（管理者）
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.show');

    // スタッフ一覧
    Route::get('/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff.index');

    // スタッフ別勤怠一覧
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])
        ->name('admin.attendance.staff');

    // 修正申請一覧（管理者）
    Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])
        ->name('admin.correction.index');

    // 修正申請承認画面（GET）（管理者）
    Route::get(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'edit']
    )->name('admin.correction.edit');

    // 修正承認・却下のPOST処理
    Route::post(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminStampCorrectionRequestController::class, 'approve']
    )->name('admin.correction.approve');
});
