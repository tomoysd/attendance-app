@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
<div class="req-page">
    <div class="req-card">
        <h1 class="req-title">申請一覧</h1>

        @php
        // ?tab=pending / ?tab=approved
        $tab = request('tab', 'pending');
        $isPendingTab = ($tab === 'pending');

        // Controllerから渡される前提
        $requests = $isPendingTab ? $pendingRequests : $approvedRequests;
        @endphp

        {{-- タブ --}}
        <div class="req-tabs">
            <a href="{{ route('admin.correction.index', ['tab' => 'pending']) }}"
                class="req-tab {{ $isPendingTab ? 'is-active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('admin.correction.index', ['tab' => 'approved']) }}"
                class="req-tab {{ !$isPendingTab ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- 一覧 --}}
        <div class="req-table-wrap">
            <table class="req-table">
                <thead>
                    <tr>
                        <th class="req-th req-th--status">状態</th>
                        <th class="req-th req-th--name">名前</th>
                        <th class="req-th req-th--target">対象日時</th>
                        <th class="req-th req-th--reason">申請理由</th>
                        <th class="req-th req-th--applied">申請日時</th>
                        <th class="req-th req-th--detail">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $req)
                    @php
                    $statusText = $isPendingTab ? '承認待ち' : '承認済み';

                    // 対象日時：attendances に work_date が無いので clock_in_at の日付で代用
                    $targetDate = optional($req->attendance)->clock_in_at
                    ? \Carbon\Carbon::parse($req->attendance->clock_in_at)->format('Y/m/d')
                    : '-';

                    $appliedAt = $req->created_at
                    ? \Carbon\Carbon::parse($req->created_at)->format('Y/m/d')
                    : '-';

                    // 「詳細」→ 承認画面（あなたのweb.phpの edit へ）
                    $detailUrl = route('admin.correction.edit', ['attendance_correct_request_id' => $req->id]);
                    @endphp

                    <tr class="req-tr">
                        <td class="req-td req-td--status">{{ $statusText }}</td>
                        <td class="req-td req-td--name">{{ optional(optional($req->attendance)->user)->name ?? '-' }}</td>
                        <td class="req-td req-td--target">{{ $targetDate }}</td>
                        <td class="req-td req-td--reason">{{ $req->reason }}</td>
                        <td class="req-td req-td--applied">{{ $appliedAt }}</td>
                        <td class="req-td req-td--detail">
                            <a class="req-detail-link" href="{{ $detailUrl }}">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="req-empty" colspan="6">表示できる申請がありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection