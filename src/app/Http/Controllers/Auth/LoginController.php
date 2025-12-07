<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Http\Requests\LoginRequest;
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
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // 直前に見ていたページがあればそこへ、なければ勤怠一覧へ
            return redirect()->intended(route('attendance.index'));
        }

        return back()
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
            ->withInput($request->only('email'));
    }
}
