<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Http\Requests\AttendanceRequest; // 承認時に勤怠を更新するなら後で使う
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請一覧（管理者）
     */
    public function index()
    {
        // TODO: 申請中のものを中心に一覧取得
        $requests = StampCorrectionRequest::with(['attendance.user'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.stamp_correction_request.index', compact('requests'));
    }

    /**
     * 修正申請内容の確認画面（承認フォーム表示）
     */
    public function edit(int $attendance_correct_request_id)
    {
        // TODO: 対象申請を取得
        $request = StampCorrectionRequest::with([
                'attendance.breaks',
                'stampCorrectionBreaks',
                'user',
            ])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction_request.edit', compact('request'));
    }

    /**
     * 承認／却下処理
     */
    public function approve(int $attendance_correct_request_id, Request $request)
    {
        $correction = StampCorrectionRequest::with(['attendance', 'stampCorrectionBreaks'])
            ->findOrFail($attendance_correct_request_id);

        // action=approve / reject などで分岐させる想定
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        if ($request->action === 'approve') {
            // 勤怠本体の更新（必要な値だけ上書き）
            $attendance = $correction->attendance;

            if ($correction->requested_clock_in_at) {
                $attendance->clock_in_at = $correction->requested_clock_in_at;
            }
            if ($correction->requested_clock_out_at) {
                $attendance->clock_out_at = $correction->requested_clock_out_at;
            }
            $attendance->save();

            // 休憩の修正（本気でやるならここで breaks を入れ替える）
            // いまは簡略化：とりあえず申請ステータスだけ更新
            $correction->status = 1; // 承認
        } else {
            $correction->status = 2; // 却下
        }

        $correction->approved_by = Auth::id();
        $correction->approved_at = Carbon::now();
        $correction->save();

        return redirect()
            ->route('admin.correction.index')
            ->with('status', '修正申請を処理しました。');
    }
}
