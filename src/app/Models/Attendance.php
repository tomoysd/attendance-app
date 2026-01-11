<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 一括代入を許可する属性
     */
    protected $fillable = [
        'user_id',
        'clock_in_at',
        'clock_out_at',
        'memo',
    ];

    /**
     * 日付・日時系は自動で Carbon にキャスト
     */
    protected $casts = [
        'clock_in_at'      => 'datetime',
        'clock_out_at'     => 'datetime',
    ];

    /**
     * この勤怠を持っているユーザー
     * attendances(N) - users(1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠に紐づく修正申請一覧
     * attendances(1) - stamp_correction_requests(N)
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * この勤怠の休憩一覧
     * attendances(1) - breaks(N)
     */
    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // 「表示に使う申請」取得
    // pending(0) が最優先、なければ approved(1)
    // ※ eager load されていればメモリ上で完結
    // -------------------------
    public function activeCorrectionRequest()
    {
        // 既にリレーションがロードされている場合はコレクションから取る（N+1回避）
        if ($this->relationLoaded('stampCorrectionRequests')) {
            $reqs = $this->stampCorrectionRequests;

            $pending  = $reqs->where('status', 0)->sortByDesc('created_at')->first();
            if ($pending) return $pending;

            $approved = $reqs->where('status', 1)->sortByDesc('created_at')->first();
            return $approved;
        }

        // 未ロードならクエリで取る
        $pending = $this->stampCorrectionRequests()
            ->where('status', 0)->latest('created_at')->first();
        if ($pending) return $pending;

        return $this->stampCorrectionRequests()
            ->where('status', 1)->latest('created_at')->first();
    }

    // pending がある時だけ編集ロック（管理者詳細の「編集不可」判定に使える）
    public function getIsLockedAttribute(): bool
    {
        if ($this->relationLoaded('stampCorrectionRequests')) {
            return $this->stampCorrectionRequests->contains(fn($r) => (int)$r->status === 0);
        }
        return $this->stampCorrectionRequests()->where('status', 0)->exists();
    }

    // -------------------------
    // 有効な出退勤（申請があれば申請、なければ通常）
    // -------------------------
    public function effectiveClockInAt()
    {
        $req = $this->activeCorrectionRequest();
        $dt  = $req?->requested_clock_in_at ?? $this->clock_in_at;
        return $dt ? $this->normalizeToMinute($dt) : null;
    }

    public function effectiveClockOutAt()
    {
        $req = $this->activeCorrectionRequest();
        $dt  = $req?->requested_clock_out_at ?? $this->clock_out_at;
        return $dt ? $this->normalizeToMinute($dt) : null;
    }

    // -------------------------
    // 有効な休憩（申請があれば申請休憩、なければ通常休憩）
    // 合計分（minutes）で返す
    // -------------------------
    public function effectiveBreakMinutes(): int
    {
        $minutes = 0;

        $req = $this->activeCorrectionRequest();

        // 申請がある場合：stampCorrectionBreaks を優先
        if ($req) {
            // 申請側休憩がリレーションで読めていればそれを使用
            $breaks = null;

            if (method_exists($req, 'stampCorrectionBreaks')) {
                if ($req->relationLoaded('stampCorrectionBreaks')) {
                    $breaks = $req->stampCorrectionBreaks;
                } else {
                    $breaks = $req->stampCorrectionBreaks()->get();
                }
            }

            $breaks = $breaks ?? collect();

            foreach ($breaks as $b) {
                if (!$b->break_start_at || !$b->break_end_at) continue;

                $bs = $this->normalizeToMinute($b->break_start_at);
                $be = $this->normalizeToMinute($b->break_end_at);

                $m = $bs->diffInMinutes($be, false);
                if ($m > 0) $minutes += $m;
            }

            return $minutes;
        }

        // 申請なし：通常休憩
        $breaks = $this->relationLoaded('breaks') ? $this->breaks : $this->breaks()->get();

        foreach ($breaks as $b) {
            if (!$b->break_start_at || !$b->break_end_at) continue;

            $bs = $this->normalizeToMinute($b->break_start_at);
            $be = $this->normalizeToMinute($b->break_end_at);

            $m = $bs->diffInMinutes($be, false);
            if ($m > 0) $minutes += $m;
        }

        return $minutes;
    }

    // -------------------------
    // Accessors（一覧で使う break_hm / total_hm を「有効値」で統一）
    // -------------------------
    public function getBreakHmAttribute(): string
    {
        $minutes = $this->effectiveBreakMinutes();
        return $minutes > 0 ? $this->formatMinutes($minutes) : '';
    }

    public function getTotalHmAttribute(): string
    {
        $ci = $this->effectiveClockInAt();
        $co = $this->effectiveClockOutAt();

        if (!$ci || !$co) return '';

        $workMinutes = $ci->diffInMinutes($co, false);
        $workMinutes = max(0, $workMinutes);

        $netMinutes = max(0, $workMinutes - $this->effectiveBreakMinutes());

        return $this->formatMinutes($netMinutes);
    }

    // -------------------------
    // Helpers
    // -------------------------
    private function normalizeToMinute($dt): Carbon
    {
        // Carbon|DateTime|string どれが来てもOK
        return Carbon::parse($dt)->setSeconds(0);
    }

    private function formatMinutes(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%d:%02d', $h, $m);
    }

    // 一般ユーザーでも使える「基準日」
    public function baseDate(): string
    {
        $base = $this->clock_in_at ?? $this->created_at ?? Carbon::now();
        return Carbon::parse($base)->toDateString();
    }

    public function correctionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    // 承認待ち（status=0）の最新1件
    public function latestPendingCorrectionRequest(): HasOne
    {
        return $this->hasOne(StampCorrectionRequest::class)
            ->where('status', 0)
            ->latestOfMany();
    }

    // 承認済み（status=1）の最新1件
    public function latestApprovedCorrectionRequest(): HasOne
    {
        return $this->hasOne(StampCorrectionRequest::class)
            ->where('status', 1)
            ->latestOfMany();
    }

    public function hasPendingCorrection(): bool
    {
        // 関連を eager load してたら exists() より軽く判定できることがあるので分岐
        if ($this->relationLoaded('latestPendingCorrectionRequest')) {
            return $this->latestPendingCorrectionRequest !== null;
        }
        return $this->correctionRequests()
            ->where('status', 0)
            ->exists();
    }

    /**
     * 「表示・集計に使う休憩」＝ 有効な休憩
     * - 承認済み申請があれば、その申請の休憩(StampCorrectionBreak)
     * - なければ通常の休憩(AttendanceBreak)
     */
    public function effectiveBreaks()
    {
        // eager load 済みならそれを使う
        if (
            $this->relationLoaded('latestApprovedCorrectionRequest')
            && $this->latestApprovedCorrectionRequest
            && $this->latestApprovedCorrectionRequest->relationLoaded('breaks')
        ) {
            return $this->latestApprovedCorrectionRequest->breaks;
        }

        // 承認済み申請を取りに行く（必要なら）
        $approved = $this->latestApprovedCorrectionRequest()
            ->with('breaks') // ← StampCorrectionRequest側に breaks() が必要（後述）
            ->first();

        if ($approved && $approved->breaks) {
            return $approved->breaks;
        }

        // 通常休憩へフォールバック
        return $this->relationLoaded('breaks') ? $this->breaks : $this->breaks()->get();
    }

    // ===== 休憩合計（分） =====
    public function breakMinutes(): int
    {
        $minutes = 0;

        foreach ($this->effectiveBreaks() as $br) {
            if (!empty($br->break_start_at) && !empty($br->break_end_at)) {
                $minutes += Carbon::parse($br->break_start_at)
                    ->diffInMinutes(Carbon::parse($br->break_end_at));
            }
        }

        return $minutes;
    }

    public function breakHm(): string
    {
        return $this->minutesToHm($this->breakMinutes());
    }

    // ===== 合計（分） =====
    public function totalMinutes(): int
    {
        if (empty($this->clock_in_at) || empty($this->clock_out_at)) {
            return 0;
        }

        $work = Carbon::parse($this->clock_in_at)->diffInMinutes(Carbon::parse($this->clock_out_at));
        return max(0, $work - $this->breakMinutes());
    }

    public function totalHm(): string
    {
        return $this->minutesToHm($this->totalMinutes());
    }

    private function minutesToHm(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%d:%02d', $h, $m);
    }
}
