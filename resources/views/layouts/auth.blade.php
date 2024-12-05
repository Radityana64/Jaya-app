<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jaya Studio - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('auth/css/auth.css') }}">
</head>
<body>
    <div class="container">
        <div class="left-side">
            <div class="logo">Jaya Studio</div>
            <div class="image-container">
                <!-- <img src="{{ asset('auth/images/handpan.png') }}" alt="Handpan" class="featured-image"> -->
                <img src="{{ asset('auth/images/Handpan.png') }}" alt="Handpan" class="featured-image">
            </div>
        </div>
        <div class="right-side">
            <div class="form-container">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('auth/js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html>