<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionBreak;
use App\Models\AttendanceBreak;

class AdminApproveCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_approve_and_attendance_is_updated(): void
    {
        // ① 管理者ユーザー
        $admin = User::factory()->create([
            // role カラムがある前提（なければ削ってOK）
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // ② 一般ユーザー & 元の勤怠
        $user = User::factory()->create(['email_verified_at' => now()]);

        $attendance = Attendance::create([
            'user_id'      => $user->id,
            'clock_in_at'  => now()->subHours(8),
            'clock_out_at' => now()->subHour(),
            'memo'         => '元のメモ',
        ]);

        // 修正申請（未承認）
        $scr = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'requested_clock_in_at' => now()->subHours(4),
            'requested_clock_out_at' => now(),
            'reason' => '電車遅延のため修正お願いします',
            'status' => 0,
        ]);

        // ⑥ 管理者として「承認POST」
        $response = $this->actingAs($admin)->post(
            route('admin.correction.approve', ['attendance_correct_request_id' => $scr->id]),
            ['action' => 'approve']
        );

        $response->assertSessionHasNoErrors();

        // 画面遷移は実装次第なので、とりあえず成功(302)だけ見る
        $response->assertStatus(302);

        // ⑦ 修正申請が「承認済み」になっている
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $scr->id,
            'status' => 1, // 承認済みに変わる想定（あなたの仕様が違えばここを合わせる）
        ]);

        // ===== 勤怠が修正後の時刻に上書きされている =====
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_at' => $scr->requested_clock_in_at,
            'clock_out_at' => $scr->requested_clock_out_at,
        ]);
    }
}
