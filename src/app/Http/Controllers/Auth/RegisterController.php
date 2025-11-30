<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * 会員登録フォーム表示
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理
     */
    public function store(Request $request)
    {
        // TODO: RegisterRequest に差し替えてバリデーション
        // TODO: ユーザー作成処理
        // TODO: ログインさせて、勤怠画面 or トップへリダイレクト

        return redirect()->route('attendance.index'); // 仮
    }
}
