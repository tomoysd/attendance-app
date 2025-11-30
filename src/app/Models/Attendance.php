<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 一括代入を許可する属性
     */
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'break1_start_at',
        'break1_end_at',
        'break2_start_at',
        'break2_end_at',
        'work_type',
        'status',
        'memo',
    ];

    /**
     * 日付・日時系は自動で Carbon にキャスト
     */
    protected $casts = [
        'work_date'        => 'date',
        'clock_in_at'      => 'datetime',
        'clock_out_at'     => 'datetime',
        'break1_start_at'  => 'datetime',
        'break1_end_at'    => 'datetime',
        'break2_start_at'  => 'datetime',
        'break2_end_at'    => 'datetime',
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
     * 休憩時間の合計（分）を返す簡易アクセサ（あっても便利）
     */
    public function getTotalBreakMinutesAttribute(): int
    {
        $total = 0;

        if ($this->break1_start_at && $this->break1_end_at) {
            $total += $this->break1_start_at->diffInMinutes($this->break1_end_at);
        }

        if ($this->break2_start_at && $this->break2_end_at) {
            $total += $this->break2_start_at->diffInMinutes($this->break2_end_at);
        }

        return $total;
    }
}
