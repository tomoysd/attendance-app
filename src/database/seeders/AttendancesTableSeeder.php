<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザーだけ取得
        $users = User::where('role', 'general')->get();

        foreach ($users as $user) {

            // 2023年6月の1ヶ月分（サンプル）
            $start = Carbon::create(2023, 6, 1);
            $end   = Carbon::create(2023, 6, 30);

            for ($date = $start; $date->lte($end); $date->addDay()) {

                // 土日は勤務しない（必要に応じて変更OK）
                if ($date->isWeekend()) {
                    continue;
                }

                // 出勤 09:00、退勤 18:00、休憩1 12:00-13:00、休憩2なし
                Attendance::create([
                    'user_id'         => $user->id,
                    'work_date'       => $date->format('Y-m-d'),

                    'clock_in_at'     => $date->copy()->setTime(9, 0),
                    'clock_out_at'    => $date->copy()->setTime(18, 0),

                    'break1_start_at' => $date->copy()->setTime(12, 0),
                    'break1_end_at'   => $date->copy()->setTime(13, 0),

                    // 休憩2は使わない例（null）
                    'break2_start_at' => null,
                    'break2_end_at'   => null,

                    'work_type'       => 0,  // 0:通常出勤
                    'status'          => 0,  // 0:通常（申請なし）
                    'memo'            => null,
                ]);
            }
        }
    }
}
