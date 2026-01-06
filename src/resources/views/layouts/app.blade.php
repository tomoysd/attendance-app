<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理システム')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- 勤怠画面用CSS --}}
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    @stack('styles')
</head>
<body class="app-body">

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();
    $routeName = Route::currentRouteName();
@endphp

<header class="app-header">
    <div class="app-header__inner">
        {{-- 左：ロゴ（auth.blade と同じ images/logo.svg を使用） --}}
        <div class="app-header__logo">
            <a href="{{ url('/') }}" class="app-header__logo-link" aria-label="COACHTECH top">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
        </div>

        {{-- 右：タブナビゲーション --}}
        <nav class="app-header__nav">
            <ul class="app-header__nav-list">
                @if ($user && $user->role === 'admin')
                    {{-- ▼ 管理者タブ：勤怠一覧 / スタッフ一覧 / 申請一覧 / ログアウト --}}
                    <li class="app-header__nav-item {{ Route::currentRouteNamed('admin.attendance.index') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.attendance.index') }}" class="app-header__nav-link">勤怠一覧</a>
                    </li>
                    <li class="app-header__nav-item {{ Route::currentRouteNamed('admin.staff.index') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.staff.index') }}" class="app-header__nav-link">スタッフ一覧</a>
                    </li>
                    <li class="app-header__nav-item {{ Route::currentRouteNamed('admin.correction.index') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.correction.index') }}" class="app-header__nav-link">申請一覧</a>
                    </li>
                    <li class="app-header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="app-header__nav-link app-header__nav-link--button">
                                ログアウト
                            </button>
                        </form>
                    </li>

                @else
                    {{-- ▼ 一般ユーザー用タブ --}}
                    @php
                        // 退勤後だけタブを切り替えたいルート名
                        $afterWorkRouteNames = ['attendance.after_work'];
                    @endphp

                    @if (in_array($routeName, $afterWorkRouteNames, true))
                        {{-- ★ 退勤後：今月の出勤一覧 / 申請一覧 / ログアウト --}}
                        <li class="app-header__nav-item {{ Route::currentRouteNamed('attendance.index') ? 'is-active' : '' }}">
                            <a href="{{ route('attendance.index') }}" class="app-header__nav-link">今月の出勤一覧</a>
                        </li>
                        <li class="app-header__nav-item {{ Route::currentRouteNamed('correction.index') ? 'is-active' : '' }}">
                            <a href="{{ route('correction.index') }}" class="app-header__nav-link">申請一覧</a>
                        </li>
                        <li class="app-header__nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="app-header__nav-link app-header__nav-link--button">
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    @else
                        {{-- ★ 通常：勤怠 / 勤怠一覧 / 申請 / ログアウト --}}
                        <li class="app-header__nav-item {{ Route::currentRouteNamed('attendance.create') ? 'is-active' : '' }}">
                            <a href="{{ route('attendance.create') }}" class="app-header__nav-link">勤怠</a>
                        </li>
                        <li class="app-header__nav-item {{ Route::currentRouteNamed('attendance.index') ? 'is-active' : '' }}">
                            <a href="{{ route('attendance.index') }}" class="app-header__nav-link">勤怠一覧</a>
                        </li>
                        <li class="app-header__nav-item {{ Route::currentRouteNamed('correction.index') ? 'is-active' : '' }}">
                            <a href="{{ route('correction.index') }}" class="app-header__nav-link">申請</a>
                        </li>
                        <li class="app-header__nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="app-header__nav-link app-header__nav-link--button">
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    @endif
                @endif
            </ul>
        </nav>
    </div>
</header>

<main class="app-main">
    @yield('content')
</main>

</body>
</html>
