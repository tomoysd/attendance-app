<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者ユーザー
        User::create([
            'name'     => '管理者 太郎',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // 一般ユーザー5名
        $generals = [
            ['name' => '西 怜奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
        ];

        foreach ($generals as $g) {
            User::create([
                'name'     => $g['name'],
                'email'    => $g['email'],
                'password' => Hash::make('password'),
                'role'     => 'general',
                'email_verified_at' => '2025/12/12',
            ]);
        }
    }
}
