<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        // 承認待ち（未承認）
        $pendingRequests = StampCorrectionRequest::with(['attendance.user'])
            ->where('status', 0) // 0: 承認待ち
            ->orderByDesc('created_at')
            ->get();

        // 承認済み
        $approvedRequests = StampCorrectionRequest::with(['attendance.user'])
            ->where('status', 1) // 1: 承認済み
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.stamp_correction_request.index', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 修正申請内容の確認画面（承認フォーム表示）
     */
    public function edit(int $attendance_correct_request_id)
    {
        // TODO: 対象申請を取得
        $request = StampCorrectionRequest::with([
            'attendance.user',
            'attendance.breaks',
            'stampCorrectionBreaks',
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
