@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endpush

@section('content')
<div class="attendance-page">
    <div class="attendance-card">
        <h1 class="attendance-title">勤怠一覧</h1>

        @php
        use Carbon\Carbon;

        // $month が未定義でも落ちない保険（本来はControllerで渡す）
        $month = $month ?? Carbon::now();

        $currentMonthText = $month->format('Y/m');
        $prevMonth = $month->copy()->subMonthNoOverflow();
        $nextMonth = $month->copy()->addMonthNoOverflow();

        // monthクエリは "YYYY-MM" で渡す想定
        $prevMonthQuery = $prevMonth->format('Y-m');
        $nextMonthQuery = $nextMonth->format('Y-m');

        // $calendar が無い場合も「表示だけ」はできるように空配列に
        $calendar = $calendar ?? [];
        @endphp

        <div class="month-nav">
            <a class="month-nav__link" href="{{ url('/attendance/list') }}?month={{ $prevMonthQuery }}">
                ← 前月
            </a>

            <div class="month-nav__center">
                <span class="month-nav__icon">📅</span>
                <span class="month-nav__label">{{ $currentMonthText }}</span>
            </div>

            <a class="month-nav__link" href="{{ url('/attendance/list') }}?month={{ $nextMonthQuery }}">
                翌月 →
            </a>
        </div>

        <div class="attendance-table-wrap">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="col-date">日付</th>
                        <th class="col-time">出勤</th>
                        <th class="col-time">退勤</th>
                        <th class="col-time">休憩</th>
                        <th class="col-time">合計</th>
                        <th class="col-detail">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($calendar as $row)
                    @php
                    $date = $row['date']; // Carbon想定
                    $dateText = $date->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$date->dayOfWeek] . ')';

                    $start = $row['start'] ?? null;
                    $end = $row['end'] ?? null;
                    $break = $row['break'] ?? null;
                    $total = $row['total'] ?? null;

                    $has = (bool)($row['has'] ?? false);

                    // 詳細リンク先（あなたの詳細画面パスに合わせて変えてOK）
                    $attendanceId = $row['attendance_id'] ?? null;
                    $detailUrl = $attendanceId ? url('/attendance/detail/' . $attendanceId) : null;
                    @endphp

                    <tr>
                        <td class="cell-date">{{ $dateText }}</td>
                        <td class="cell-time">{{ $start ?? '' }}</td>
                        <td class="cell-time">{{ $end ?? '' }}</td>
                        <td class="cell-time">{{ $break ?? '' }}</td>
                        <td class="cell-time">{{ $total ?? '' }}</td>
                        <td class="cell-detail">
                            @if($attendanceId)
                            <a class="detail-link" href="{{ $detailUrl }}">詳細</a>
                            @else
                            <span class="detail-link detail-link--disabled">詳細</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="empty-row" colspan="6">この月の勤怠データがありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection