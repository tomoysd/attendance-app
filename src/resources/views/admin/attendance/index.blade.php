@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endpush

@section('content')
<div class="admin-attendance">
    <div class="admin-attendance__inner">
        <h1 class="admin-attendance__title">
            {{ \Carbon\Carbon::parse($targetDate)->format('Y年n月j日') }}の勤怠
        </h1>

        <div class="admin-attendance__nav">
            <a class="admin-attendance__nav-link" href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}">← 前日</a>

            <div class="admin-attendance__date">
                <span class="admin-attendance__date-icon">📅</span>
                <span class="admin-attendance__date-text">
                    {{ \Carbon\Carbon::parse($targetDate)->format('Y/m/d') }}
                </span>
            </div>

            <a class="admin-attendance__nav-link" href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}">翌日 →</a>
        </div>

        <div class="admin-attendance__table-wrap">
            <table class="admin-attendance__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['user_name'] }}</td>

                        {{-- 未打刻は空白 --}}
                        <td>{{ $row['clock_in_at'] ? \Carbon\Carbon::parse($row['clock_in_at'])->format('H:i') : '' }}</td>
                        <td>{{ $row['clock_out_at'] ? \Carbon\Carbon::parse($row['clock_out_at'])->format('H:i') : '' }}</td>
                        <td>{{ $row['break_hm'] }}</td>
                        <td>{{ $row['total_hm'] }}</td>

                        <td>
                            @if($row['attendance_id'])
                            <a class="admin-attendance__detail-link"
                                href="{{ route('admin.attendance.show', $row['attendance_id']) }}">
                                詳細
                            </a>
                            @else
                            {{-- 勤怠レコード自体が無いなら空白（UI要件） --}}
                            <span class="admin-attendance__detail-link admin-attendance__detail-link--disabled">詳細</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection