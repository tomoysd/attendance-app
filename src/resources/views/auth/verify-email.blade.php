@extends('layouts.auth')

@section('content')
<div class="verify-wrap">
    <p class="verify-text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    {{-- MailHog へのリンク --}}
    <a href="http://localhost:8025" target="_blank" rel="noopener" class="verify-btn">認証はこちらから</a>

    {{-- 再送リンク --}}
    <form method="POST" action="{{ route('verification.send') }}" class="verify-resend">
        @csrf
        <button type="submit" class="verify-resend__link">認証メールを再送する</button>
    </form>

    @if (session('status') === 'verification-link-sent')
    <p class="verify-status">認証メールを再送しました。受信ボックスをご確認ください。</p>
    @endif
</div>
@endsection