<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 管理者ログインフォーム表示
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        // TODO: LoginRequest でバリデーション
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // roleがadminでなければログアウトさせる
            if (! Auth::user()->isAdmin()) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => '管理者権限がありません。'])
                    ->withInput($request->only('email'));
            }

        return redirect()->route('admin.attendance.index');
    }
    return back()
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
            ->withInput($request->only('email'));
    }
}
