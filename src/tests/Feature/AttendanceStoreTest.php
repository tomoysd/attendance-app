<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verified_user_can_store_attendance(): void
    {
        // ① メール認証済みユーザーを用意
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ② 出勤保存
        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // バリデーション落ちしてないか（落ちてたら原因確定）
        $response->assertSessionHasNoErrors();

        // ③ 保存されたこと（attendances に user_id のレコードがある）
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        // ④ 画面遷移の結果はアプリ次第なので、とりあえず「エラーじゃない」ことだけ保証
        // （リダイレクト設計なら 302 が普通）
        $response->assertStatus(302);
    }
}
