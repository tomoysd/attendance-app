<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreakStartEndTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verified_user_can_start_break_and_create_break_record(): void
    {
        // ✅ メール認証済みユーザー
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ✅ 出勤済み勤怠（clock_in_atがある状態）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_at' => now()->subHours(1),
            'clock_out_at' => null,
            'memo' => null,
        ]);

        // ✅ 休憩開始POST（あなたの実装に合わせて action 名は調整してOK）
        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        // ✅ バリデーションで落ちてないこと
        $response->assertSessionHasNoErrors();

        // ✅ breaks に「休憩開始」が1件できていること
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        // clock_out などと同様に、基本302は自然（リダイレクト）
        $response->assertStatus(302);
    }

    /** @test */
    public function verified_user_can_end_break_and_fill_break_end_at(): void
    {
        // ✅ メール認証済みユーザー
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ✅ 出勤済み勤怠
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_at' => now()->subHours(1),
            'clock_out_at' => null,
            'memo' => null,
        ]);

        // ✅ まず休憩開始（breakレコードを作る）
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ])->assertSessionHasNoErrors();

        // ✅ 休憩終了POST
        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);

        $response->assertSessionHasNoErrors();

        // ✅ breaks テーブルに「break_end_at が入ったレコード」が存在すること
        // （※break_end_at を DB に保存してる実装が前提）
        $this->assertDatabaseMissing('breaks', [
            'attendance_id' => $attendance->id,
            'break_end_at' => null,
        ]);

        $response->assertStatus(302);
    }
}
