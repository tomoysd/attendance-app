<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    /**
     * 出勤登録フォーム表示
     */
    public function create()
    {
        return view('attendance.register');
    }

    /**
     * 出勤登録処理
     */
    public function store(Request $request)
    {
        // TODO: AttendanceRequest でバリデーション
        $request->validate([
            'clock_in_at'  => ['required', 'date'],
            'clock_out_at' => ['required', 'date', 'after:clock_in_at'],
            // 休憩1個分だけ入力する想定（画面仕様に合わせて後で増やせる）
            'break_start_at' => ['nullable', 'date'],
            'break_end_at'   => ['nullable', 'date', 'after:break_start_at'],
            'memo'           => ['nullable', 'string', 'max:255'],
        ]);

        // 勤怠本体の作成
        $attendance = Attendance::create([
            'user_id'      => Auth::id(),
            'clock_in_at'  => $request->clock_in_at,
            'clock_out_at' => $request->clock_out_at,
            'memo'         => $request->memo,
        ]);

        // 休憩（1件だけ）※複数にしたければここを配列処理に変更
        if ($request->filled('break_start_at') && $request->filled('break_end_at')) {
            AttendanceBreak::create([
                'attendance_id'  => $attendance->id,
                'break_start_at' => $request->break_start_at,
                'break_end_at'   => $request->break_end_at,
            ]);
        }

        return redirect()->route('attendance.index')
            ->with('status', '勤怠を登録しました。');
    }

    /**
     * ログインユーザーの勤怠一覧（月単位）
     */
    public function index(Request $request)
    {
        // ?month=2023-06 のようなクエリ。なければ今月
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::with(['breaks'])
            ->where('user_id', Auth::id())
            ->whereBetween('clock_in_at', [$start, $end])
            ->orderBy('clock_in_at')
            ->get();

        return view('attendance.index', [
            'attendances' => $attendances,
            'month'       => $month,
            'start'       => $start,
            'end'         => $end,
        ]);
    }

    /**
     * 勤怠詳細
     */
    public function show(int $id)
    {
        $attendance = Attendance::with(['breaks'])
            ->where('user_id', Auth::id()) // 他人の勤怠は見れないようにする
            ->findOrFail($id);

        return view('attendance.show', compact('attendance'));
    }
}
