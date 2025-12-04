<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;

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
}
