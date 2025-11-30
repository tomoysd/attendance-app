<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請一覧（管理者）
     */
    public function index()
    {
        // TODO: 申請中のものを中心に一覧取得
        // $requests = StampCorrectionRequest::with(['attendance', 'user'])->get();

        return view('admin.stamp_correction_request.index'/*, compact('requests')*/);
    }

    /**
     * 修正申請内容の確認画面（承認フォーム表示）
     */
    public function edit(int $attendance_correct_request_id)
    {
        // TODO: 対象申請を取得
        // $request = StampCorrectionRequest::with(['attendance', 'user'])->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction_request.edit'/*, compact('request')*/);
    }

    /**
     * 承認／却下処理
     */
    public function approve(int $attendance_correct_request_id/*, AttendanceRequest $request*/)
    {
        // TODO: 申請のステータス更新＆勤怠の修正処理
        // TODO: 承認者・承認日時の保存

        return redirect()->route('admin.correction.index');
    }
}
