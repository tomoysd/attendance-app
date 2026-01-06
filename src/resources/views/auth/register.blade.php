@extends('layouts.auth')

@section('title', '会員登録画面（一般ユーザー）')

@section('page_label', '会員登録画面（一般ユーザー）')

@section('content')
    <section class="auth-card">
        <h1 class="auth-title">会員登録</h1>

        {{-- エラーメッセージ --}}
        @if ($errors->any())
            <div class="auth-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="auth-form">
            @csrf

            <div class="auth-form-group">
                <label for="name" class="auth-label">名前</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="auth-input"
                    autocomplete="name"
                >
            </div>

            <div class="auth-form-group">
                <label for="email" class="auth-label">メールアドレス</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="auth-input"
                    autocomplete="email"
                >
            </div>

            <div class="auth-form-group">
                <label for="password" class="auth-label">パスワード</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="auth-input"
                    autocomplete="new-password"
                >
            </div>

            <div class="auth-form-group">
                <label for="password_confirmation" class="auth-label">パスワード確認</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="auth-input"
                    autocomplete="new-password"
                >
            </div>

            <div class="auth-form-actions">
                <button type="submit" class="auth-button auth-button--primary">
                    登録する
                </button>
            </div>

            <div class="auth-link-under">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </form>
    </section>
@endsection
