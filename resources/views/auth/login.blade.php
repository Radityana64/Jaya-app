@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1>Sign in</h1>
    <p class="auth-link">Belum Punya Akun? <a href="{{ route('register') }}">Register</a></p>

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="loginForm" action="{{ route('login.submit') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">
                    <!-- <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath d='M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z'/%3E%3C/svg%3E" alt="Show password" class="eye-icon"> -->
                </button>
            </div>
        </div>

        <button type="submit" class="submit-btn">Login</button>

        <div class="forgot-password">
            <a href="{{ route('forgot.password') }}">Forgot password?</a>
        </div>
    </form>
@endsection