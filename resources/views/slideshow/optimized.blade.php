<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Mading Slideshow</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('css/slideshow.css') }}" as="style">
    <link rel="preload" href="{{ asset('js/slideshow.js') }}" as="script">
    
    <!-- Critical CSS -->
    <link rel="stylesheet" href="{{ asset('css/slideshow.css') }}">
    
    <!-- Meta tags untuk SEO dan performance -->
    <meta name="description" content="Digital Mading Slideshow - Tampilan media interaktif">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Security headers -->
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body>
    <div class="slideshow-container" id="slideshow-container">
        <div class="loading" id="loading">
            <div class="loading-spinner"></div>
            <div>Memuat slideshow...</div>
        </div>
        
        <!-- Error message container -->
        <div class="error-message" id="error-message" style="display: none;">
            <div class="error-content">
                <h2>Tidak ada media tersedia</h2>
                <p>Silakan tambahkan media melalui admin panel untuk memulai slideshow.</p>
            </div>
        </div>
    </div>
    
    <div class="status-indicator" id="status-indicator"></div>

    <!-- JavaScript -->
    <script src="{{ asset('js/slideshow.js') }}" defer></script>
    
    <!-- Analytics atau tracking code bisa ditambahkan di sini -->
    @if(config('app.env') === 'production')
    <script>
        // Production-only code
        console.log = function() {}; // Disable console.log in production
    </script>
    @endif
</body>
</html>
