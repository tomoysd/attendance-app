<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    /**
     * スタッフ一覧
     */
    public function index()
    {
        // TODO: 一般ユーザーだけ取得（role = general）
        $users = User::where('role', 'general')
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', compact('users'));
    }
}
