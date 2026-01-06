@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_staff.css') }}">
@endpush

@section('content')
<div class="admin-staff-attendance">

    <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

    <div class="month-nav">
        <a class="month-nav__btn" href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}">
            ←前月
        </a>

        <div class="month-nav__center">
            <span class="month-nav__icon">📅</span>
            <span class="month-nav__ym">{{ $displayYm }}</span>
        </div>

        <a class="month-nav__btn" href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}">
            翌月→
        </a>
    </div>

    <div class="table-wrap">
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
                @foreach($days as $row)
                <tr>
                    <td class="col-date">
                        {{ $row['date']->format('m/d') }}({{ $row['date']->isoFormat('ddd') }})
                    </td>
                    <td class="col-time">{{ $row['clock_in'] }}</td>
                    <td class="col-time">{{ $row['clock_out'] }}</td>
                    <td class="col-time">{{ $row['break_hm'] }}</td>
                    <td class="col-time">{{ $row['total_hm'] }}</td>

                    <td class="col-detail">
                        @if($row['attendance'])
                        <a class="detail-link" href="{{ route('admin.attendance.show', ['id' => $row['attendance']->id]) }}">詳細</a>
                        @else
                        <span class="detail-link detail-link--disabled">詳細</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="csv-area">
        <a class="csv-btn"
            href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => \Carbon\Carbon::parse($displayYm.'/01')->format('Y-m')]) }}">
            CSV出力
        </a>
    </div>

</div>
@endsection