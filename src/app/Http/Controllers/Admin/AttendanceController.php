<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;

class AttendanceController extends Controller
{
    /**
     * 全スタッフの勤怠一覧（管理者）
     */
    public function index()
    {
        // TODO: 月指定で全スタッフの勤怠を取得
        // $attendances = Attendance::with('user')->get();

        return view('admin.attendance.index'/*, compact('attendances')*/);
    }

    /**
     * 勤怠詳細（管理者）
     */
    public function show(int $id)
    {
        // TODO: 対象勤怠を取得
        // $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.attendance.show'/*, compact('attendance')*/);
    }

    /**
     * スタッフ別勤怠一覧（管理者）
     */
    public function staff(int $id)
    {
        // TODO: 対象ユーザーと、そのユーザーの勤怠一覧を取得
        // $user = User::findOrFail($id);
        // $attendances = $user->attendances()->get();

        return view('admin.attendance.staff'/*, compact('user', 'attendances')*/);
    }
}
