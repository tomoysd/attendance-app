<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampCorrectionRequestStoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verified_user_can_create_stamp_correction_request(): void
    {
        // ✅ メール認証済みユーザー
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ✅ 既に勤怠レコードがある前提（修正申請は attendance_id に紐づく）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_at' => now()->subHours(2),
            'clock_out_at' => null,
            'memo' => null,
        ]);

        // ✅ 修正申請POST（ここ超重要：/attendance/detail/{id}）
        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [

            'memo' => '電車遅延のため修正お願いします',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end'   => ['13:00'],
        ]);

        // ✅ バリデーション落ちしてない
        $response->assertSessionHasNoErrors();

        // ✅ DBに保存された（status は初期値が入る想定）
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'reason' => '電車遅延のため修正お願いします',
            'status' => 0,
        ]);

        // 画面遷移はアプリ次第なので「とりあえず動いた」最低保証
        $response->assertStatus(302);
    }
}
