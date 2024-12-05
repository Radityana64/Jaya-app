@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h1>Forgot Password</h1>

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

    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="submit-btn">Send Password Reset Link</button>
    </form>

    <div class="forgot-password">
        <p>Remember your password? <a href="{{ route('login') }}">Sign In</a></p>
    </div>
@endsection