<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    /**
     * ログインユーザーの修正申請一覧(承認待ち / 承認済み)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // タブ（見た目切替用）
        $tab = $request->query('tab', 'pending'); // 'pending' or 'approved'
        if (!in_array($tab, ['pending', 'approved'], true)) {
            $tab = 'pending';
        }

        // user_id は stamp_correction_requests に無いので attendance を経由して絞り込む
        $baseQuery = StampCorrectionRequest::query()
            ->with([
                'attendance',         // 勤怠
                'attendance.user',    // 一覧で名前出すなら
            ])
            ->whereHas('attendance', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderByDesc('created_at');

        // 承認待ち
        $pendingRequests = (clone $baseQuery)
            ->where('status', 0)
            ->get();

        // 承認済み
        $approvedRequests = (clone $baseQuery)
            ->where('status', 1)
            ->get();

        return view('stamp_correction_request.index', compact(
            'tab',
            'pendingRequests',
            'approvedRequests'
        ));
    }
}
