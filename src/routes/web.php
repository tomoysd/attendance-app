<?php

use Illuminate\Support\Facades\Route;

// 一般ユーザー用コントローラ
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AttendanceController;
use App\Http\Controllers\Auth\StampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// 管理者用コントローラ
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;


Route::get('/', function () {
    return redirect()->route('login');
});
/*
|--------------------------------------------------------------------------
| 一般ユーザー：認証不要
|--------------------------------------------------------------------------
*/

//会員登録（GET / POST）
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

//ログイン（GET / POST）
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);


Route::middleware('auth')->group(function () {
// メール認証誘導画面（未認証のときここに飛ぶ）
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // メール認証リンクを踏んだとき
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // email_verified_at が入る
        return redirect()->route('attendance.create'); // 認証後に行きたい場所へ
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    // 認証メール再送
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});


/*
|--------------------------------------------------------------------------
| 一般ユーザー：ログイン後のみ
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // 出勤登録画面（勤怠登録）
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/attendance/after-work', [AttendanceController::class, 'afterWork'])
        ->name('attendance.after_work');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 修正申請（保存）
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'requestCorrection'])
        ->name('attendance.correction');

    // 修正申請一覧（表示）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('correction.index');
    // 修正申請（登録）
    Route::post('/stamp_correction_request', [StampCorrectionRequestController::class, 'store'])
        ->name('correction.store');

});


/*
|--------------------------------------------------------------------------
| 管理者：ログイン後のみ
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {

    //管理者ログイン
    Route::get('/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'store'])->name('admin.login.store');

    Route::middleware('admin')->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

        // 勤怠一覧（管理者）
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('admin.attendance.index');

        // 勤怠詳細（管理者）
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.show');

        // 勤怠修正（「修正」ボタンで更新）
        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');

        // スタッフ一覧
        Route::get('/staff/list', [AdminStaffController::class, 'index'])
            ->name('admin.staff.index');

        // スタッフ別勤怠一覧
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])
            ->name('admin.attendance.staff');

        // ★CSV出力
        Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffCsv'])
            ->name('admin.attendance.staff.csv');

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
});
