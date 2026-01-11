@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')

<h1 class="att-detail-title">勤怠詳細</h1>

@if (session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif


{{-- エラー表示（要件：メッセージ表示） --}}
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="att-detail-page">
    <div class="att-detail-card">
        <form method="POST" action="{{ route('attendance.correction', ['id' => $attendance->id]) }}">
            @csrf

            <div class="att-table">
                <div class="att-row">
                    <div class="att-th">名前</div>
                    <div class="att-td att-td--text">{{ $user->name }}</div>
                </div>

                <div class="att-row">
                    <div class="att-th">日付</div>
                    <div class="att-td att-td--date">
                        <span class="att-date-year">{{ $yearText }}</span>
                        <span class="att-date-md">{{ $mdText }}</span>
                    </div>
                </div>

                <div class="att-row">
                    <div class="att-th">出勤・退勤</div>
                    <div class="att-td att-td--range">
                        <input type="time" name="clock_in" value="{{ $clockInValue }}" class="att-time" {{ $isReadOnly ? 'disabled' : '' }}>
                        <span class="att-range-tilde">〜</span>
                        <input type="time" name="clock_out" value="{{ $clockOutValue }}" class="att-time" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
                </div>

                {{-- 休憩：休憩回数分 + 追加1枠 --}}
                @for ($i = 0; $i < $breakRowsCount; $i++)
                    @php
                    $label=($i===0) ? '休憩' : '休憩' . ($i + 1);

                    $bs=$breakRows[$i]['start'] ?? '' ;
                    $be=$breakRows[$i]['end'] ?? '' ;
                    @endphp

                    <div class="att-row">
                    <div class="att-th">{{ $label }}</div>
                    <div class="att-td att-td--range">
                        <input type="time" name="break_start[]" value="{{ $bs }}" class="att-time" {{ $isReadOnly ? 'disabled' : '' }}>
                        <span class="att-range-tilde">〜</span>
                        <input type="time" name="break_end[]" value="{{ $be }}" class="att-time" {{ $isReadOnly ? 'disabled' : '' }}>
                    </div>
            </div>
            @endfor

            <div class="att-row">
                <div class="att-th">備考</div>
                <div class="att-td att-td--memo">
                    <textarea name="memo" class="att-memo" rows="3" {{ $isReadOnly ? 'disabled' : '' }}>{{ $memoValue }}</textarea>
                </div>
            </div>
    </div>
</div>
</div>
{{-- 承認待ちのメッセージ --}}
@if ($hasPending)
<div class="alert alert-warning">
    *承認待ちのため修正はできません。
</div>
@endif

{{-- 承認待ちのときは「修正」ボタンを出さない --}}
<div class="att-actions">
    @if (!$hasPending)
        <button type="submit" class="btn-fix">修正</button>
    @endif
</div>
</form>

@endsection