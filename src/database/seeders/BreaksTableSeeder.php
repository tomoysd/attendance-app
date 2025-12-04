<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // すべての勤怠に対して 12:00〜13:00 の休憩を1つ付けるサンプル
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            AttendanceBreak::create([
                'attendance_id'  => $attendance->id,
                'break_start_at' => $attendance->clock_in_at->copy()->setTime(12, 0),
                'break_end_at'   => $attendance->clock_in_at->copy()->setTime(13, 0),
            ]);
        }
    }
}
