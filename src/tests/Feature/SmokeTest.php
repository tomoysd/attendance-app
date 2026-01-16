<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class SmokeTest extends TestCase
{
    /** @test */
    public function login_page_is_ok()
    {
        $this->get('/login')->assertStatus(200);
    }

    /** @test */
    public function register_page_is_ok()
    {
        $this->get('/register')->assertStatus(200);
    }

    /** @test */
    public function root_redirects_when_guest()
    {
        $this->get('/')->assertStatus(302);
        // リダイレクト先まで固定するなら（必要なら）：
        // $this->get('/')->assertRedirect('/login');
    }

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        // 未ログインならログインへ飛ばされるのが正しい
        $response->assertRedirect('/login');
        // もしくは $response->assertStatus(302); でもOK
    }

    public function test_attendance_page_ok_when_logged_in_and_verified(): void
    {
        $user = User::factory()->create([
            // verified 必須なのでここが超重要
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
    }
}
