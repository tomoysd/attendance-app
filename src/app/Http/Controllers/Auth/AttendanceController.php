<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionBreak;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class AttendanceController extends Controller
{
    /**
     * 出勤登録フォーム表示
     */
    public function create()
    {
        $userId = Auth::id();
        $today  = Carbon::today();

        // 今日の勤怠（1日1レコード前提）
        $attendance = Attendance::with(['breaks' => function ($q) {
            $q->orderBy('id', 'asc');
        }])
            ->where('user_id', $userId)
            ->whereDate('clock_in_at', $today)
            ->first();

        // state 判定
        if (!$attendance) {
            $state = 'before_work';
        } elseif (!is_null($attendance->clock_out_at)) {
            $state = 'after_work'; // 退勤済
        } else {
            // 退勤してない＝出勤中 or 休憩中
            $hasOpenBreak = $attendance->breaks()
                ->whereNull('break_end_at')
                ->exists();

            $state = $hasOpenBreak ? 'on_break' : 'working';
        }

        // 表示用（例：2023年6月1日(木)）
        Carbon::setLocale('ja');
        $workDate = $today;
        $dateText = $workDate->isoFormat('YYYY年M月D日(ddd)');
        $timeText = Carbon::now()->format('H:i');

        return view('attendance.create', compact('state', 'dateText', 'timeText'));
    }

    /**
     * 出勤登録処理
     */
    public function store(Request $request)
    {
        // TODO: AttendanceRequest でバリデーション
        $request->validate([
            'action' => ['required', 'in:clock_in,clock_out,break_start,break_end'],
        ]);

        $userId = Auth::id();
        $today  = Carbon::today();
        $now    = Carbon::now();

        return DB::transaction(function () use ($request, $userId, $today, $now) {

            // 今日の勤怠（無ければnull）
            $attendance = Attendance::with('breaks')
                ->where('user_id', $userId)
                ->whereDate('clock_in_at', $today)
                ->lockForUpdate()
                ->first();

            $action = $request->input('action');

            // 1) 出勤
            if ($action === 'clock_in') {
                if ($attendance) {
                    // すでに今日出勤済み
                    return redirect()->route('attendance.create')
                        ->with('status', '本日はすでに出勤済みです。');
                }

                Attendance::create([
                    'user_id'     => $userId,
                    'clock_in_at' => $now,
                    'clock_out_at' => null,
                    'memo'        => null,
                ]);

                return redirect()->route('attendance.create')
                    ->with('status', '出勤しました。');
            }

            // 出勤してないのに、休憩/退勤を押した
            if (!$attendance) {
                return redirect()->route('attendance.create')
                    ->with('status', '先に出勤してください。');
            }

            // すでに退勤済みなのに何か押した
            if (!is_null($attendance->clock_out_at)) {
                return redirect()->route('attendance.after_work')
                    ->with('status', '本日の勤怠はすでに終了しています。');
            }

            // 2) 休憩入
            if ($action === 'break_start') {
                $hasOpenBreak = $attendance->breaks()
                    ->whereNull('break_end_at')
                    ->exists();

                if ($hasOpenBreak) {
                    return redirect()->route('attendance.create')
                        ->with('status', 'すでに休憩中です。');
                }

                AttendanceBreak::create([
                    'attendance_id'  => $attendance->id,
                    'break_start_at' => $now,
                    'break_end_at'   => null,
                ]);

                return redirect()->route('attendance.create')
                    ->with('status', '休憩に入りました。');
            }

            // 3) 休憩戻
            if ($action === 'break_end') {
                $openBreak = $attendance->breaks()
                    ->whereNull('break_end_at')
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if (!$openBreak) {
                    return redirect()->route('attendance.create')
                        ->with('status', '休憩中ではありません。');
                }

                $openBreak->update([
                    'break_end_at' => $now,
                ]);

                return redirect()->route('attendance.create')
                    ->with('status', '休憩から戻りました。');
            }

            // 4) 退勤
            if ($action === 'clock_out') {
                // 休憩中（open breakあり）なら退勤させない方が自然
                $hasOpenBreak = $attendance->breaks()
                    ->whereNull('break_end_at')
                    ->exists();

                if ($hasOpenBreak) {
                    return redirect()->route('attendance.create')
                        ->with('status', '休憩中は退勤できません。先に休憩戻を押してください。');
                }

                $attendance->update([
                    'clock_out_at' => $now,
                ]);

                return redirect()->route('attendance.after_work')
                    ->with('status', 'お疲れ様でした。');
            }

            // ここには来ない想定
            return redirect()->route('attendance.create');
        });
    }

    /**
     * 退勤後画面
     */
    public function afterWork()
    {
        Carbon::setLocale('ja');
        $dateText = Carbon::today()->isoFormat('YYYY年M月D日(ddd)');
        $timeText = Carbon::now()->format('H:i');

        return view('attendance.after_work', compact('dateText', 'timeText'));
    }

    /**
     * ログインユーザーの勤怠一覧（月単位）
     */
    public function index(Request $request)
    {
        // ?month=2023-06 のようなクエリ。なければ今月
        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));

        // "Y-m" 文字列 → Carbon（表示月）
        $month = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // clock_in_at が datetime なので、月の範囲で取得（自分の分だけ）
        $attendances = Attendance::with(['breaks'])
            ->where('user_id', Auth::id())
            ->whereBetween('clock_in_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->get();

        // 日付（YYYY-MM-DD）で引けるようにする
        $byDate = $attendances->keyBy(function ($a) {
            return Carbon::parse($a->clock_in_at)->toDateString();
        });

        // 1日ごとの表示行（空白の日も含む）を作る
        $calendar = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->toDateString();
            $attendance = $byDate->get($key);

            $startText = $attendance?->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : null;
            $endText   = $attendance?->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : null;

            // ---- 休憩合計（breaksの合計分） ----
            $breakMinutes = 0;

            if ($attendance) {
                foreach ($attendance->breaks as $br) {
                    if ($br->break_start_at && $br->break_end_at) {
                        $breakMinutes += Carbon::parse($br->break_start_at)
                            ->diffInMinutes(Carbon::parse($br->break_end_at));
                    }
                }
            }

            $breakText = $breakMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : null;

            // ---- 合計（出勤〜退勤 − 休憩） ----
            $totalText = null;

            if ($attendance?->clock_in_at && $attendance?->clock_out_at) {
                $workMinutes = Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                $netMinutes = max(0, $workMinutes - $breakMinutes); // マイナス防止

                $totalText = sprintf('%d:%02d', intdiv($netMinutes, 60), $netMinutes % 60);
            }


            $calendar[] = [
                'date' => $date,
                'start' => $startText,
                'end' => $endText,
                'break' => $breakText,
                'total' => $totalText,

                // ★詳細遷移用（/attendance/detail/{id}）
                'attendance_id' => $attendance?->id,
            ];
        }

        return view('attendance.index', [
            'month' => $month,       // Carbonで渡す
            'calendar' => $calendar, // Bladeはこれを回す
        ]);
    }

    /**
     * 勤怠詳細
     */
    public function show(int $id)
    {
        $attendance = Attendance::with(['breaks'])
            ->where('user_id', Auth::id()) // 他人の勤怠は見れないようにする
            ->findOrFail($id);

        // URLパラメータでモード判定
        $pendingMode  = request()->boolean('pending');   // ?pending=1
        $approvedMode = request()->boolean('approved');  // ?approved=1

        // 申請レコード（pending or approved で取得先を変える）
        $requestQuery = StampCorrectionRequest::with(['stampCorrectionBreaks'])
            ->where('attendance_id', $attendance->id);

        $pendingRequest = null;
        $approvedRequest = null;

        if ($pendingMode) {
            $pendingRequest = (clone $requestQuery)
                ->where('status', 0)
                ->latest('id')
                ->first();
        }

        if ($approvedMode) {
            $approvedRequest = (clone $requestQuery)
                ->where('status', 1)
                ->latest('id')
                ->first();
        }

        // 画面に使う申請（優先順位：approvedMode → pendingMode → なし）
        $activeRequest = $approvedRequest ?: $pendingRequest;

        $isPending  = (bool) $pendingRequest;
        $isApproved = (bool) $approvedRequest;

        // 編集不可条件：承認待ち表示 or 承認済み表示
        $isReadOnly = $isPending || $isApproved;

        // 出勤・退勤（申請があれば申請値、なければ勤怠値）
        $clockInAt  = $activeRequest ? $activeRequest->requested_clock_in_at  : $attendance->clock_in_at;
        $clockOutAt = $activeRequest ? $activeRequest->requested_clock_out_at : $attendance->clock_out_at;

        $clockInValue  = $clockInAt  ? Carbon::parse($clockInAt)->format('H:i')  : '';
        $clockOutValue = $clockOutAt ? Carbon::parse($clockOutAt)->format('H:i') : '';

        // 備考：申請があれば reason、なければ attendance memo
        $memoValue = $activeRequest
            ? ($activeRequest->reason ?? '')
            : ($attendance->memo ?? '');

        // 休憩：申請があれば stampCorrectionBreaks、なければ breaks
        $displayBreaks = $activeRequest
            ? ($activeRequest->stampCorrectionBreaks ?? collect())
            : ($attendance->breaks ?? collect());

        // BladeでCarbon使わないように「表示用配列」に変換
        $breakRows = [];
        foreach ($displayBreaks as $b) {
            $breakRows[] = [
                'start' => $b->break_start_at ? Carbon::parse($b->break_start_at)->format('H:i') : '',
                'end'   => $b->break_end_at   ? Carbon::parse($b->break_end_at)->format('H:i')   : '',
            ];
        }
        $breakRowsCount = count($breakRows) + 1; // 追加1行

        // 日付表示
        $date = $attendance->clock_in_at
            ? Carbon::parse($attendance->clock_in_at)
            : Carbon::parse($attendance->created_at);

        $yearText = $date->format('Y年');
        $mdText   = $date->format('n月j日');

        $user = Auth::user();

        // ボタン表示（承認済みは「承認済み」にしたい）
        $buttonLabel = $isApproved ? '承認済み' : '修正';

        return view('attendance.show', [
            'attendance' => $attendance,
            'user' => $user,

            // モード
            'isPending' => $isPending,
            'isApproved' => $isApproved,
            'isReadOnly' => $isReadOnly,

            // 表示用確定値
            'clockInValue' => $clockInValue,
            'clockOutValue' => $clockOutValue,
            'memoValue' => $memoValue,
            'breakRows' => $breakRows,
            'breakRowsCount' => $breakRowsCount,

            'yearText' => $yearText,
            'mdText' => $mdText,

            // ボタン
            'buttonLabel' => $buttonLabel,
        ]);
    }

    // 修正申請 実行（一般）
    public function requestCorrection(AttendanceCorrectionRequest $request, int $id)
    {
        $attendance = Attendance::with(['breaks'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // 承認待ちがあるなら弾く
        $alreadyPending = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 0)
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withInput()
                ->withErrors(['pending' => '承認待ちのため修正はできません。']);
        }

        // 入力された時刻（H:i）を attendance の日付に結合して datetime にする
        $workDate = Carbon::parse($attendance->clock_in_at ?? $attendance->created_at)->toDateString();

        $clockIn  = $request->filled('clock_in')  ? Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $request->clock_in)  : null;
        $clockOut = $request->filled('clock_out') ? Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $request->clock_out) : null;

        $scr = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'requested_clock_in_at' => $clockIn,
            'requested_clock_out_at' => $clockOut,
            'reason' => $request->memo,
            'status' => 0, // 承認待ち
            'approved_by' => null,
            'approved_at' => null,
        ]);

        // 休憩（配列）保存：入力がある行だけ登録
        $breakStarts = $request->input('break_start', []);
        $breakEnds   = $request->input('break_end', []);

        $count = max(count($breakStarts), count($breakEnds));
        for ($i = 0; $i < $count; $i++) {
            $bs = $breakStarts[$i] ?? null;
            $be = $breakEnds[$i] ?? null;

            if (!$bs && !$be) continue; // 両方空ならスキップ

            $bsDt = $bs ? Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $bs) : null;
            $beDt = $be ? Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $be) : null;

            StampCorrectionBreak::create([
                'stamp_correction_request_id' => $scr->id,
                'break_start_at' => $bsDt,
                'break_end_at' => $beDt,
            ]);
        }

        return redirect()->route('correction.index', ['id' => $attendance->id])
            ->with('status', '修正申請を送信しました。');
    }
}
