<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // TODO: 管理者用 guard or role チェックしてログイン
        // if (Auth::attempt([...]) && Auth::user()->isAdmin()) { ... }

        return redirect()->route('admin.attendance.index'); // 仮
    }
}
