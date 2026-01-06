@extends('layouts.auth')

@section('title', 'ログイン画面（管理者）')

@section('page_label', 'ログイン画面（管理者）')

@section('content')
    <section class="auth-card">
        <h1 class="auth-title">管理者ログイン</h1>

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

        <form action="{{ route('admin.login.store') }}" method="POST" class="auth-form">
            @csrf

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
                    autocomplete="current-password"
                >
            </div>

            <div class="auth-form-actions">
                <button type="submit" class="auth-button auth-button--primary">
                    管理者ログインする
                </button>
            </div>
        </form>
    </section>
@endsection
