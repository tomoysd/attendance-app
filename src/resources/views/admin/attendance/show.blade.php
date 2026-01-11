@extends('layouts.app')

@push('styles')
{{-- 一般ユーザー側の詳細CSSを流用 --}}
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')


<h1 class="att-detail-title">勤怠詳細</h1>


{{-- 成功メッセージ --}}
@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- ✅ 承認待ちメッセージは「承認待ちのときだけ」 --}}
@if ($hasPending)
<div class="alert alert-warning-top">
    *承認待ちのため修正はできません。
</div>
@endif

{{-- バリデーション --}}
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

        <form method="POST" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}">
            @csrf
            @method('PATCH')


            <div class="att-table">
                {{-- 名前 --}}
                <div class="att-row">
                    <div class="att-th">名前</div>
                    <div class="att-td att-td--text">
                        {{ $attendance->user->name ?? '-' }}
                    </div>
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
                        <input type="time"
                            name="clock_in"
                            value="{{ old('clock_in', $clockIn) }}"
                            class="att-time"
                            {{ $isLocked ? 'disabled' : '' }}>

                        <span class="att-range-tilde">〜</span>

                        <input type="time"
                            name="clock_out"
                            value="{{ old('clock_out', $clockOut) }}"
                            class="att-time"
                            {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>

                {{-- 休憩（複数） --}}
                @foreach ($breakRows as $i => $row)
                <div class="att-row">
                    <div class="att-th">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </div>

                    <div class="att-td att-td--range">
                        {{-- id（既存休憩の更新用） --}}
                        <input type="hidden"
                            name="breaks[{{ $i }}][id]"
                            value="{{ old("breaks.$i.id", $row['id']) }}">

                        <input type="time"
                            name="breaks[{{ $i }}][start]"
                            value="{{ old("breaks.$i.start", $row['start']) }}"
                            class="att-time"
                            {{ $isLocked ? 'disabled' : '' }}>

                        <span class="att-range-tilde">〜</span>

                        <input type="time"
                            name="breaks[{{ $i }}][end]"
                            value="{{ old("breaks.$i.end", $row['end']) }}"
                            class="att-time"
                            {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>
                @endforeach

                {{-- 備考 --}}
                <div class="att-row">
                    <div class="att-th">備考</div>

                    <div class="att-td att-td--memo">
                        <textarea name="memo"
                            class="att-memo"
                            rows="3"
                            {{ $isLocked ? 'disabled' : '' }}>{{ old('memo', $memo) }}</textarea>
                    </div>
                </div>
            </div>
    </div>
</div>
{{-- ✅ 修正ボタンは編集できる時だけ表示 --}}
@if(!$isLocked)
<div class="att-actions">
    <button type="submit" class="btn-fix">修正</button>
</div>
@endif

{{-- ✅ 承認済みバッジ --}}
@if($hasApproved)
<div class="att-actions">
    <span class="btn-approved">承認済み</span>
</div>
@endif
</form>


@endsection