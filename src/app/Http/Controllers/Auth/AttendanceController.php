<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;


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
        // TODO: ログインユーザーの勤怠を保存する処理

        return redirect()->route('attendance.index'); // 仮
    }

    /**
     * ログインユーザーの勤怠一覧
     */
    public function index()
    {
        // TODO: ログインユーザーの勤怠を月ごとに取得
        // $attendances = Attendance::where('user_id', Auth::id())->get();

        return view('attendance.index'/*, compact('attendances')*/);
    }

    /**
     * 勤怠詳細
     */
    public function show(int $id)
    {
        // TODO: 対象勤怠を取得して本人かどうかチェック
        // $attendance = Attendance::where('user_id', Auth::id())->findOrFail($id);

        return view('attendance.show'/*, compact('attendance')*/);
    }
}
