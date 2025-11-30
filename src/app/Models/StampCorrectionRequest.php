<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in_at',
        'requested_clock_out_at',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_clock_in_at' => 'datetime',
        'requested_clock_out_at'=> 'datetime',
        'approved_at'           => 'datetime',
    ];

    /**
     * 対象の勤怠
     * stamp_correction_requests(N) - attendances(1)
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 申請したユーザー（一般ユーザー）
     * stamp_correction_requests(N) - users(1)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 承認した管理ユーザー
     * stamp_correction_requests(N) - users(1) （approved_by 経由）
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ステータス名を返す簡易アクセサ（0/1/2 を文字に）
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1       => '承認',
            2       => '却下',
            default => '申請中',
        };
    }
}
