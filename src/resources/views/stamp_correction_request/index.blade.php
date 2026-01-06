@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
<div class="req-page">
    <div class="req-card">
        <h1 class="req-title">申請一覧</h1>

        @php
        // ?tab=pending / ?tab=approved で切り替え
        $tab = request('tab', 'pending');
        $isPendingTab = ($tab === 'pending');

        // Controllerから $pendingRequests / $approvedRequests を渡す想定
        $requests = $isPendingTab ? $pendingRequests : $approvedRequests;
        @endphp

        {{-- タブ --}}
        <div class="req-tabs">
            <a href="{{ route('correction.index', ['tab' => 'pending']) }}"
                class="req-tab {{ $isPendingTab ? 'is-active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('correction.index', ['tab' => 'approved']) }}"
                class="req-tab {{ !$isPendingTab ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- 一覧 --}}

        <div class="req-table-wrap">
            <table class="req-table">
                <thead>
                    <tr>
                        <th class="req-th">状態</th>
                        <th class="req-th">名前</th>
                        <th class="req-th">対象日時</th>
                        <th class="req-th">申請理由</th>
                        <th class="req-th">申請日時</th>
                        <th class="req-th req-th--detail">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $req)
                    @php
                    $statusText = $req->status_label ?? ($isPendingTab ? '承認待ち' : '承認済み');

                    // 「対象日時」= 勤怠の日付（attendanceのclock_in_at想定）
                    $targetSource = optional($req->attendance)->clock_in_at
                    ?? optional($req->attendance)->created_at;

                    $targetDate = $targetSource
                    ? \Carbon\Carbon::parse($targetSource)->format('Y/m/d')
                    : '';


                    // 「申請日時」
                    $appliedAt = $req->created_at
                    ? \Carbon\Carbon::parse($req->created_at)->format('Y/m/d')
                    : '';

                    // 詳細は「勤怠詳細」へ
                    // 承認待ちは「修正不可」表示したいので ?pending=1 を付ける（show側で判定に使える）
                    $detailUrl = route('attendance.show', ['id' => $req->attendance_id]) . ($isPendingTab ? '?pending=1' : '?approved=1');
                    @endphp

                    <tr class="req-tr">
                        <td class="req-td req-td--status">{{ $statusText }}</td>
                        <td class="req-td">{{ $req->user->name ?? auth()->user()->name }}</td>
                        <td class="req-td">{{ $targetDate }}</td>
                        <td class="req-td req-td--reason">{{ $req->reason }}</td>
                        <td class="req-td">{{ $appliedAt }}</td>
                        <td class="req-td req-td--detail">
                            <a class="req-detail-link" href="{{ $detailUrl }}">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="req-empty" colspan="6">
                            表示できる申請がありません。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection