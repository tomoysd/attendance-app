<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
// use App\Http\Requests\RegisterRequest; // あとでFormRequestに差し替え
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // TODO: ユーザー作成処理
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'general',   // 一般ユーザーとして登録
        ]);

        // 登録直後にログイン
        Auth::login($user);

        // TODO: ログインさせて、勤怠画面 or トップへリダイレクト
        return redirect()->route('attendance.index');
    }
}
