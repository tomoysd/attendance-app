<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * ログインフォーム表示
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function store(Request $request)
    {
        // TODO: LoginRequest に差し替えてバリデーション

        // TODO: 認証処理（guard('web') を使う想定）
        // if (Auth::attempt($request->only('email', 'password'))) {
        //     $request->session()->regenerate();
        //     return redirect()->intended(route('attendance.index'));
        // }

        // TODO: 失敗時のエラーメッセージ表示

        return back(); // 仮
    }
}
