<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AccessControlTest extends TestCase
{
    /** @test */
    public function guest_cannot_access_attendance_page(): void
    {
        // 未ログインで /attendance に行くとログインへ飛ばされる（302）
        $this->get('/attendance')->assertStatus(302);
        // もし遷移先まで固定したいなら（環境により /login とは限らないので任意）
        // $this->get('/attendance')->assertRedirect('/login');
    }

    /** @test */
    public function logged_in_but_not_verified_user_cannot_access_attendance_page(): void
    {
        // ログインはしているがメール未認証
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(302); // verifiedミドルウェアによりリダイレクト

        // 余裕があれば遷移先も確認（Laravelの実装や設定で変わるので、まずはStatusだけでOK）
        // $this->actingAs($user)->get('/attendance')->assertRedirect('/email/verify');
    }

    /** @test */
    public function verified_user_can_access_attendance_page(): void
    {
        // ログイン済 + メール認証済
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertStatus(200);
    }
}
