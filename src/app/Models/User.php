<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 一般ユーザーかどうか
     */
    public function isGeneral(): bool
    {
        return $this->role === 'general';
    }

    /**
     * 管理者ユーザーかどうか
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * このユーザーの勤怠一覧
     * users(1) - attendances(N)
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * このユーザーが申請した勤怠修正申請一覧
     * users(1) - stamp_correction_requests(N) （申請者として）
     */
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * このユーザーが承認者として関わった勤怠修正申請一覧
     * users(1) - stamp_correction_requests(N) （approved_by 経由）
     */
    public function approvedStampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class, 'approved_by');
    }
}
