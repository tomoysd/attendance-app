<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 全スタッフの勤怠一覧（管理者）
     */
    public function index()
    {
        // TODO: 月指定で全スタッフの勤怠を取得
        $month = request('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::with(['user'])
            ->whereBetween('clock_in_at', [$start, $end])
            ->orderBy('clock_in_at')
            ->get();

        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'month'       => $month,
            'start'       => $start,
            'end'         => $end,
        ]);
    }

    /**
     * 勤怠詳細（管理者）
     */
    public function show(int $id)
    {
        // TODO: 対象勤怠を取得
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * スタッフ別勤怠一覧（管理者）
     */
    public function staff(int $id)
    {
        // TODO: 対象ユーザーと、そのユーザーの勤怠一覧を取得
        $user = User::where('role', 'general')->findOrFail($id);

        $month = request('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = $user->attendances()
            ->whereBetween('clock_in_at', [$start, $end])
            ->orderBy('clock_in_at')
            ->get();

        return view('admin.attendance.staff', [
            'user'        => $user,
            'attendances' => $attendances,
            'month'       => $month,
            'start'       => $start,
            'end'         => $end,
        ]);
    }
}
