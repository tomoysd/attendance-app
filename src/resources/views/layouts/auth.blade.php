<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '勤怠管理システム' }}</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<header class="auth-header">
    <div class="auth-header__inner">
        <!-- ロゴ -->
            <a href="{{ url('/') }}" class="logo" aria-label="COACHTECH top">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" />
            </a>
    </div>
</header>

<main class="auth-main">
    @yield('content')
</main>
</body>
</html>
