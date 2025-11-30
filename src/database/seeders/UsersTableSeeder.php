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
            ['name' => '西 怜奈', 'email' => 'reina@example.com'],
            ['name' => '山田 太郎', 'email' => 'taro@example.com'],
            ['name' => '増田 一世', 'email' => 'issei@example.com'],
            ['name' => '山本 敬吾', 'email' => 'keigo@example.com'],
            ['name' => '秋田 開美', 'email' => 'akemi@example.com'],
        ];

        foreach ($generals as $g) {
            User::create([
                'name'     => $g['name'],
                'email'    => $g['email'],
                'password' => Hash::make('password'),
                'role'     => 'general',
            ]);
        }
    }
}
