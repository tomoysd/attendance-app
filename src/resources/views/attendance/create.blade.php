@extends('layouts.app')

@section('title', '勤怠登録')

@section('content')
{{--
        Controller 側から以下を渡してもらう想定：
        $state        : 'before_work' | 'working' | 'on_break'
        $dateText     : Carbon（日付）
        $timeText  : string or Carbon（08:00 など表示用）
    --}}
@php
// Controllerから来なければ「今日」で埋める（未定義エラー防止）
$workDate = $workDate ?? now();

$state = $state ?? 'before_work';
$dateText = $workDate->translatedFormat('Y年n月j日(D)');
$timeText = $displayTime ?? now()->format('H:i');

$stateLabel = [
'before_work' => '勤務外',
'working' => '出勤中',
'on_break' => '休憩中',
'after_work' => '退勤済',
][$state] ?? '勤務外';
@endphp

<section class="attendance-card">
    <div class="attendance-card__inner">
        {{-- 状態ラベル（勤務外 / 出勤中 / 休憩中） --}}
        <p class="attendance-chip">{{ $stateLabel }}</p>

        {{-- 日付 --}}
        <p class="attendance-date">{{ $dateText }}</p>

        {{-- 時刻 --}}
        <p class="attendance-time">{{ $timeText }}</p>

        {{-- ボタンエリア --}}
        <div class="attendance-actions">
            @if ($state === 'before_work')
            {{-- 勤務外：出勤ボタンだけ --}}
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="clock_in">
                <button type="submit" class="attendance-button attendance-button--primary">
                    出勤
                </button>
            </form>

            @elseif ($state === 'working')
            {{-- 出勤中：退勤ボタン＋休憩入リンク --}}
            <form action="{{ route('attendance.store') }}" method="POST" class="attendance-form-main">
                @csrf
                <input type="hidden" name="action" value="clock_out">
                <button type="submit" class="attendance-button attendance-button--primary">
                    退勤
                </button>
            </form>

            <form action="{{ route('attendance.store') }}" method="POST" class="attendance-form-sub">
                @csrf
                <input type="hidden" name="action" value="break_start">
                <button type="submit" class="attendance-button attendance-link-button">
                    休憩入
                </button>
            </form>

            @elseif ($state === 'on_break')
            {{-- 休憩中：休憩戻ボタンだけ --}}
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="break_end">
                <button type="submit" class="attendance-button attendance-button--primary">
                    休憩戻
                </button>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection