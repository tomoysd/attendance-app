<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
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
    public function store(RegisterRequest $request)
    {
        // TODO: RegisterRequest に差し替えてバリデーション
        $request->validated();

        // TODO: ユーザー作成処理
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'general',   // 一般ユーザーとして登録
        ]);

        event(new Registered($user));
        // 登録直後にログイン
        Auth::login($user);

        // TODO: ログインさせて、勤怠画面
        return redirect()->route('attendance.create');
    }
}
