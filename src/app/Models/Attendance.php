<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    // 休憩合計（HH:MM）
    public function getBreakHmAttribute(): string
    {
        $seconds = $this->breaks->sum(function ($b) {
            if (!$b->break_start_at || !$b->break_end_at) return 0;
            return $b->break_end_at->diffInSeconds($b->break_start_at);
        });

        return $this->formatSeconds($seconds);
    }

    // 合計（出勤〜退勤 - 休憩）（HH:MM）
    public function getTotalHmAttribute(): string
    {
        if (!$this->clock_in_at || !$this->clock_out_at) return '';

        $workSeconds = $this->clock_out_at->diffInSeconds($this->clock_in_at);

        $breakSeconds = $this->breaks->sum(function ($b) {
            if (!$b->break_start_at || !$b->break_end_at) return 0;
            return $b->break_end_at->diffInSeconds($b->break_start_at);
        });

        $net = max(0, $workSeconds - $breakSeconds);

        return $this->formatSeconds($net);
    }

    private function formatSeconds(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        return sprintf('%d:%02d', $h, $m);
    }

    // 承認待ち(例: status=0)の申請があるか
    public function hasPendingCorrection(): bool
    {
        return $this->stampCorrectionRequests()->where('status', 0)->exists();
    }

    // 更新用：基準日（datetimeの“日付”部分）
    public function baseDate(): string
    {
        // clock_in_at があればその日付、無ければ created_at
        $base = $this->clock_in_at ?? $this->created_at ?? Carbon::now();
        return Carbon::parse($base)->toDateString(); // "YYYY-MM-DD"
    }
}
