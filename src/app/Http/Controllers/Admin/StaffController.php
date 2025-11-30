<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    /**
     * スタッフ一覧
     */
    public function index()
    {
        // TODO: 一般ユーザーだけ取得（role = general）
        // $users = User::where('role', 'general')->get();

        return view('admin.staff.index'/*, compact('users')*/);
    }
}
