@extends('layouts.app')

@section('title', '勤怠登録（退勤後）')

@section('content')
@php
// Controllerから来なければ「今日」で埋める（未定義エラー防止）
$workDate = $workDate ?? now();

$state = $state ?? 'before_work';
$dateText = $workDate->translatedFormat('Y年n月j日(D)');
$timeText = $displayTime ?? now()->format('H:i');
@endphp

<section class="attendance-card">
    <div class="attendance-card__inner">
        {{-- 状態ラベル：退勤後 --}}
        <p class="attendance-chip">退勤済</p>

        {{-- 日付 --}}
        <p class="attendance-date">{{ $dateText }}</p>

        {{-- 時刻（退勤時刻） --}}
        <p class="attendance-time">{{ $timeText }}</p>

        {{-- メッセージ --}}
        <p class="attendance-message">お疲れ様でした。</p>
    </div>
</section>
@endsection