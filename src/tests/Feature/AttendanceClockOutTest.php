<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verified_user_can_clock_out_attendance(): void
    {
        // ✅ メール認証済みユーザー
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ✅ すでに「出勤済み」の勤怠を用意（clock_in_at が入っている状態）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clock_in_at' => now()->subHours(1),
            'clock_out_at' => null,
            'memo' => null,
        ]);

        // ✅ 退勤POST
        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        // ✅ バリデーションで落ちてない（落ちたらここで原因確定しやすい）
        $response->assertSessionHasNoErrors();

        // ✅ DBに「退勤時刻」が入ったこと（レコードは同じidのまま更新される想定）
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
        ]);

        // clock_out_at は値の一致チェックが難しいので「nullじゃない」を追加で保証
        $this->assertNotNull(Attendance::find($attendance->id)->clock_out_at);

        // ✅ 画面遷移はアプリ次第なので、とりあえず「エラーじゃない」だけ保証
        $response->assertStatus(302);
    }
}
