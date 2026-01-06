<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // まず通常ログインを試す（webガード）
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // ★ここがB方式の肝：roleがadminじゃなければ弾く
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()
                    ->withErrors(['login' => 'ログイン情報が登録されていません'])
                    ->onlyInput('email');
            }

            return redirect()->route('admin.attendance.index');
        }

        return back()
            ->withErrors(['login' => 'ログイン情報が登録されていません'])
            ->onlyInput('email');
    }

    public function destroy()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
