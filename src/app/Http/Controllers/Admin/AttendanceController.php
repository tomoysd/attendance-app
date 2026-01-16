<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * 全スタッフの勤怠一覧（管理者）
     */
    public function index(Request $request)
    {
        // /admin/attendance/list?date=YYYY-MM-DD
        $dateStr = $request->query('date', now()->toDateString());

        try {
            $targetDate = Carbon::parse($dateStr)->startOfDay();
        } catch (\Throwable) {
            $targetDate = now()->startOfDay();
        }

        $date = $targetDate->toDateString();

        // 全ユーザー + 対象日の勤怠を取得（clock_in_at の日付で判定）
        // さらに：勤怠に紐づく申請（承認待ち/承認済み）と、申請休憩も一緒に読む
        $users = User::query()
            ->orderBy('id')
            ->with(['attendances' => function ($q) use ($date) {
                $q->whereDate('clock_in_at', $date)
                    ->with([
                        'breaks',
                        'stampCorrectionRequests' => function ($rq) {
                            $rq->latest('created_at')
                                ->with(['stampCorrectionBreaks' => fn($bq) => $bq->orderBy('break_start_at')]);
                        },
                    ]);
            }])
            ->get();

        $rows = $users->map(function ($user) {
            $attendance = $user->attendances->first(); // その日の勤怠（なければ null）

            // 勤怠がない日は空行
            if (!$attendance) {
                return [
                    'user_name'      => $user->name,
                    'clock_in_at'    => null,
                    'clock_out_at'   => null,
                    'break_hm'       => '',
                    'total_hm'       => '',
                    'attendance_id'  => null,
                ];
            }

            // 表示用の出退勤は「有効値」を使う（申請があれば申請、なければ通常）
            $ci = $attendance->effectiveClockInAt();
            $co = $attendance->effectiveClockOutAt();

            return [
                'user_name'     => $user->name,
                'clock_in_at'   => $ci?->format('H:i'),
                'clock_out_at'  => $co?->format('H:i'),
                'break_hm'      => $attendance->break_hm, // モデル側で「有効休憩」を計算
                'total_hm'      => $attendance->total_hm, // モデル側で「有効合計」を計算
                'attendance_id' => $attendance->id,
            ];
        });

        return view('admin.attendance.index', [
            'rows'       => $rows,
            'targetDate' => $targetDate->toDateString(),
            'prevDate'   => $targetDate->copy()->subDay()->toDateString(),
            'nextDate'   => $targetDate->copy()->addDay()->toDateString(),
        ]);
    }


    /**
     * 勤怠詳細（管理者）
     */
    public function show(Attendance $id)
    {
        $attendance = $id;
        $attendance->load([
            'user',
            'breaks' => fn($q) => $q->orderBy('break_start_at'),
        ]);
        // 承認待ち（status=0）の申請を1件取得（最新）
        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', 0)
            ->latest('created_at')
            ->with(['stampCorrectionBreaks' => fn($q) => $q->orderBy('break_start_at')])
            ->first();

        $approvedRequest = $attendance->stampCorrectionRequests()
            ->where('status', 1)
            ->latest('created_at')
            ->with(['stampCorrectionBreaks' => fn($q) => $q->orderBy('break_start_at')])
            ->first();

        // 状態を分ける
        $hasPending  = (bool) $pendingRequest;
        $hasApproved = (bool) $approvedRequest;

        // 表示は「承認済み優先」→なければ承認待ち→なければ通常
        $activeRequest = $approvedRequest ?? $pendingRequest;

        //  編集不可の判定：承認待ち or 承認済み ならロック
        $isLocked = $hasPending || $hasApproved;


        // 日付表示（勤怠ベースでOK：申請でも同日想定）
        $base = $attendance->clock_in_at ?? $attendance->created_at;
        $date = Carbon::parse($base);
        $yearText = $date->format('Y年');
        $mdText   = $date->format('n月j日');

        // ===== 表示用の値を「どっちから取るか」分岐 =====
        if ($activeRequest) {
            // 承認待ち：申請内容を表示
            $clockIn  = $activeRequest->requested_clock_in_at ? Carbon::parse($activeRequest->requested_clock_in_at)->format('H:i') : '';
            $clockOut = $activeRequest->requested_clock_out_at ? Carbon::parse($activeRequest->requested_clock_out_at)->format('H:i') : '';
            $memo     = $activeRequest->reason ?? '';

            // 申請側の休憩
            $breakRows = collect($activeRequest->stampCorrectionBreaks ?? [])->map(function ($b) {
                return [
                    'id'    => $b->id,
                    'start' => $b->break_start_at ? Carbon::parse($b->break_start_at)->format('H:i') : '',
                    'end'   => $b->break_end_at   ? Carbon::parse($b->break_end_at)->format('H:i') : '',
                ];
            })->values()->toArray();
        } else {
            // 申請なし：通常勤怠を表示（編集可）
            $clockIn  = $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '';
            $clockOut = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '';
            $memo     = $attendance->memo ?? '';

            $breakRows = $attendance->breaks->map(function ($b) {
                return [
                    'id'    => $b->id,
                    'start' => $b->break_start_at ? Carbon::parse($b->break_start_at)->format('H:i') : '',
                    'end'   => $b->break_end_at   ? Carbon::parse($b->break_end_at)->format('H:i') : '',
                ];
            })->values()->toArray();

            // 休憩が0件でも表示用に1行
            if (count($breakRows) === 0) {
                $breakRows[] = ['id' => null, 'start' => '', 'end' => ''];
            }
        }

        return view('admin.attendance.show', compact(
            'attendance',
            'isLocked',
            'hasPending',
            'hasApproved',
            'yearText',
            'mdText',
            'clockIn',
            'clockOut',
            'breakRows',
            'memo'
        ));
    }

    public function update(AttendanceUpdateRequest $request, Attendance $id)
    {
        $attendance = $id;
        // 承認待ち申請があるなら編集不可
        if ($attendance->hasPendingCorrection()) {
            return back()
                ->withInput()
                ->with('locked_message', '承認待ちのため修正はできません。');
        }

        $baseDate = $attendance->baseDate(); // "YYYY-MM-DD"

        // "HH:ii" → "YYYY-MM-DD HH:ii:00"
        $toDateTime = function (?string $time) use ($baseDate): ?string {
            if (!$time) return null;
            return Carbon::parse("$baseDate $time")->format('Y-m-d H:i:s');
        };

        DB::transaction(function () use ($request, $attendance, $toDateTime) {

            // attendance 更新
            $attendance->update([
                'clock_in_at' => $toDateTime($request->input('clock_in')),
                'clock_out_at' => $toDateTime($request->input('clock_out')),
                'memo' => $request->input('memo'),
            ]);

            // breaks 同期（更新/追加/削除）
            $rows = $request->input('breaks', []);

            foreach ($rows as $row) {
                $id = $row['id'] ?? null;
                $start = $row['start'] ?? null;
                $end = $row['end'] ?? null;

                // 両方空なら「削除」扱い（既存idがある場合）
                if (!$start && !$end) {
                    if ($id) {
                        AttendanceBreak::where('id', $id)
                            ->where('attendance_id', $attendance->id)
                            ->delete();
                    }
                    continue;
                }

                // 片方だけ入力は Request 側で弾かれている前提
                $payload = [
                    'attendance_id' => $attendance->id,
                    'break_start_at' => $toDateTime($start),
                    'break_end_at' => $toDateTime($end),
                ];

                if ($id) {
                    AttendanceBreak::where('id', $id)
                        ->where('attendance_id', $attendance->id)
                        ->update($payload);
                } else {
                    AttendanceBreak::create($payload);
                }
            }
        });

        return redirect()
            ->route('admin.attendance.show', $attendance)
            ->with('success', '勤怠を修正しました。');
    }

    /**
     * スタッフ別勤怠一覧（管理者）
     */
    public function staff(Request $request, int $id)
    {
        //  対象ユーザーと、そのユーザーの勤怠一覧を取得
        $user = User::where('role', 'general')->findOrFail($id);

        $month = $request->query('month');
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        // 対象月の勤怠（clock_in_atがその月のもの）
        $attendances = Attendance::with([
            'breaks',
            'latestApprovedCorrectionRequest.breaks',
            'latestPendingCorrectionRequest',         // （ロック表示にも使える）
        ])
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_at')
            ->whereBetween('clock_in_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get();

        // 日付キー（Y-m-d）で引けるようにする
        $attendanceMap = $attendances->keyBy(function ($a) {
            return Carbon::parse($a->clock_in_at)->toDateString();
        });

        // 月の日付一覧を作る（1日〜末日）
        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $attendance = $attendanceMap->get($key);

            // 表示用に整形（無い日は空欄）
            $clockIn  = $attendance?->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '';
            $clockOut = $attendance?->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '';

            $breakHm = $attendance ? $attendance->breakHm() : '';
            $totalHm = $attendance ? $attendance->totalHm() : '';

            $days[] = [
                'date' => $d->copy(),          // Carbon
                'attendance' => $attendance,   // null or Attendance
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break_hm' => $breakHm,
                'total_hm' => $totalHm,
            ];
        }

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');
        $displayYm = $start->format('Y/m');

        return view('admin.attendance.staff', compact(
            'user',
            'days',
            'displayYm',
            'prevMonth',
            'nextMonth'
        ));
    }

    // ★CSV出力：表示中の月のデータをCSVでDL
    public function exportStaffCsv(Request $request, int $id): StreamedResponse
    {
        $user = User::findOrFail($id);

        $month = $request->query('month');
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereNotNull('clock_in_at')
            ->whereBetween('clock_in_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->clock_in_at)->toDateString());

        $filename = sprintf('%s_%s_attendance.csv', $user->name, $start->format('Y-m'));

        return response()->streamDownload(function () use ($start, $end, $attendances) {
            $out = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計']);

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->toDateString();
                $a = $attendances->get($key);

                $clockIn  = $a?->clock_in_at ? Carbon::parse($a->clock_in_at)->format('H:i') : '';
                $clockOut = $a?->clock_out_at ? Carbon::parse($a->clock_out_at)->format('H:i') : '';

                $breakMinutes = 0;
                if ($a) {
                    foreach ($a->breaks as $br) {
                        if ($br->break_start_at && $br->break_end_at) {
                            $breakMinutes += Carbon::parse($br->break_start_at)->diffInMinutes(Carbon::parse($br->break_end_at));
                        }
                    }
                }
                $breakHm = $a ? $this->minutesToHm($breakMinutes) : '';

                $totalHm = '';
                if ($a && $a->clock_in_at && $a->clock_out_at) {
                    $workMinutes = Carbon::parse($a->clock_in_at)->diffInMinutes(Carbon::parse($a->clock_out_at));
                    $totalHm = $this->minutesToHm(max(0, $workMinutes - $breakMinutes));
                }

                fputcsv($out, [
                    $d->format('m/d(D)'),
                    $clockIn,
                    $clockOut,
                    $breakHm,
                    $totalHm,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function minutesToHm(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%d:%02d', $h, $m);
    }
}
