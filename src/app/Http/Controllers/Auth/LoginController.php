<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
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
    public function store(LoginRequest $request)
    {
        $request->validated();

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // 直前に見ていたページがあればそこへ、なければ勤怠画面へ
            return redirect()->intended(route('attendance.create'));
        }

        return back()
            ->withErrors(['email' => 'ログイン情報が登録されていません'])
            ->withInput($request->only('email'));
    }
}
