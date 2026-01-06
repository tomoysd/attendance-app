@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_staff.css') }}">
@endpush

@section('content')
<div class="admin-staff">
    <div class="admin-staff__inner">
        <h1 class="admin-staff__title">スタッフ一覧</h1>

        {{-- フラッシュ（任意） --}}
        @if (session('success'))
        <p class="admin-staff__flash admin-staff__flash--success">
            {{ session('success') }}
        </p>
        @endif

        @if ($errors->any())
        <div class="admin-staff__errors">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="admin-staff__table-wrap">
            <table class="admin-staff__table">
                <thead>
                    <tr>
                        <th class="admin-staff__th admin-staff__th--name">名前</th>
                        <th class="admin-staff__th admin-staff__th--email">メールアドレス</th>
                        <th class="admin-staff__th admin-staff__th--link">月次勤怠</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                    <tr class="admin-staff__tr">
                        <td class="admin-staff__td admin-staff__td--name">
                            {{ $user->name }}
                        </td>

                        <td class="admin-staff__td admin-staff__td--email">
                            {{ $user->email }}
                        </td>

                        <td class="admin-staff__td admin-staff__td--link">
                            <a class="admin-staff__detail-link"
                                href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="admin-staff__tr">
                        <td class="admin-staff__td admin-staff__td--empty" colspan="3">
                            スタッフが見つかりません。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection