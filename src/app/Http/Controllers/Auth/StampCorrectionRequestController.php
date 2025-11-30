<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    /**
     * ログインユーザーの修正申請一覧
     */
    public function index()
    {
        // TODO: ログインユーザーの申請一覧を取得
        // $requests = StampCorrectionRequest::where('user_id', Auth::id())->get();

        return view('stamp_correction_request.index'/*, compact('requests')*/);
    }
}
