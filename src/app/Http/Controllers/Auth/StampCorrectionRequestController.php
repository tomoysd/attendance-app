<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    /**
     * ログインユーザーの修正申請一覧
     */
    public function index()
    {
        $requests = StampCorrectionRequest::with(['attendance'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.index', compact('requests'));
    }
}
