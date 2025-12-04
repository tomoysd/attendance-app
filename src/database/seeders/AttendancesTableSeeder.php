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

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                // 土日は勤務しない（必要に応じて変更OK）
                if ($date->isWeekend()) {
                    continue;
                }

                // 出勤 09:00、退勤 18:00、休憩1 12:00-13:00、休憩2なし
                Attendance::create([
                    'user_id'         => $user->id,
                    'clock_in_at'     => $date->copy()->setTime(9, 0),
                    'clock_out_at'    => $date->copy()->setTime(18, 0),
                    'memo'            => null,
                ]);
            }
        }
    }
}
