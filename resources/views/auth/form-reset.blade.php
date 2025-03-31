@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h1>Reset Password</h1>
    <div class="auth-link">
        Masukkan password baru Anda
    </div>

    @if(session('status'))
        <div class="success-message">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="error-message">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('password.update', $token) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                minlength="8"
                autocomplete="new-password"
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="submit-btn">
            Reset Password
        </button>
    </form>

    <div class="forgot-password">
        <a href="{{ route('login') }}">Kembali ke Login</a>
    </div>
@endsection