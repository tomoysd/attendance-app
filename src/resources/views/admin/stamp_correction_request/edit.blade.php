@extends('layouts.app')

@push('styles')
{{-- 勤怠詳細と同じCSSを流用 --}}
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')
@php
use Illuminate\Support\Carbon;

/** @var \App\Models\StampCorrectionRequest $request */
$attendance = $request->attendance;

// 表示日（clock_in_at が無ければ created_at）
$date = $attendance?->clock_in_at
? Carbon::parse($attendance->clock_in_at)
: Carbon::parse($attendance?->created_at);

$yearText = $date->format('Y年');
$mdText = $date->format('n月j日');

// 申請の「修正後（requested）」があればそれを優先して表示
$clockIn = $request->requested_clock_in_at
? Carbon::parse($request->requested_clock_in_at)->format('H:i')
: ($attendance?->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '--:--');

$clockOut = $request->requested_clock_out_at
? Carbon::parse($request->requested_clock_out_at)->format('H:i')
: ($attendance?->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '--:--');

// 休憩：修正申請に紐づく休憩があればそれを表示、無ければ勤怠の休憩
$breaks = ($request->stampCorrectionBreaks && $request->stampCorrectionBreaks->count() > 0)
? $request->stampCorrectionBreaks
: ($attendance?->breaks ?? collect());

// 画面仕様に合わせて「休憩」「休憩2」まで最低2行は出す
$breakRows = $breaks->map(function ($b) {
return [
'start' => $b->break_start_at ? Carbon::parse($b->break_start_at)->format('H:i') : '--:--',
'end' => $b->break_end_at ? Carbon::parse($b->break_end_at)->format('H:i') : '--:--',
];
})->values()->all();


    $userName = $attendance?->user?->name ?? '-';
    $memo = $attendance?->memo ?? '';
    @endphp

    <h1 class="att-detail-title">勤怠詳細</h1>

    {{-- エラーメッセージ --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="att-detail-page">
        <div class="att-detail-card">
            <div class="att-table">

                {{-- 名前 --}}
                <div class="att-row">
                    <div class="att-th">名前</div>
                    <div class="att-td att-td--text">{{ $userName }}</div>
                </div>

                {{-- 日付 --}}
                <div class="att-row">
                    <div class="att-th">日付</div>
                    <div class="att-td att-td--date">
                        <span class="att-date-year">{{ $yearText }}</span>
                        <span class="att-date-md">{{ $mdText }}</span>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="att-row">
                    <div class="att-th">出勤・退勤</div>
                    <div class="att-td att-td--range">
                        <span class="att-time att-time--static">{{ $clockIn }}</span>
                        <span class="att-range-tilde">〜</span>
                        <span class="att-time att-time--static">{{ $clockOut }}</span>
                    </div>
                </div>

                {{-- 休憩（休憩 / 休憩2） --}}
                @foreach ($breakRows as $i => $row)
                <div class="att-row">
                    <div class="att-th">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>
                    <div class="att-td att-td--range">
                        <span class="att-time att-time--static">{{ $row['start'] }}</span>
                        <span class="att-range-tilde">〜</span>
                        <span class="att-time att-time--static">{{ $row['end'] }}</span>
                    </div>
                </div>
                @endforeach

                {{-- 備考（表示専用） --}}
                <div class="att-row">
                    <div class="att-th">備考</div>
                    <div class="att-td att-td--memo">
                        <textarea class="att-memo" rows="3" disabled>{{ $request->reason }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- 承認ボタン --}}
    <form method="POST" action="{{ route('admin.correction.approve', ['attendance_correct_request_id' => $request->id]) }}">
        @csrf
        <input type="hidden" name="action" value="approve">

        <div class="att-actions">
            @if($request->status === 1)
            <button type="button" class="btn-fix btn-approved" disabled>承認済み</button>
            @else
            <button type="submit" class="btn-fix">承認</button>
            @endif
        </div>
    </form>

    @endsection