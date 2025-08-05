<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mading Digital</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .slideshow-links {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        .slideshow-link {
            display: block;
            padding: 20px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
            text-align: center;
        }
        .slideshow-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .slideshow-link h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .slideshow-link p {
            margin: 0;
            font-size: 14px;
            opacity: 0.8;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .feature-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
        }
        .feature-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        .feature-item:before {
            content: "✅ ";
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Dashboard Mading Digital</h1>
        
        <div class="info">
            <strong>Slideshow Media</strong> - Tampilan media interaktif untuk mading digital Anda
        </div>

        <div class="slideshow-links">
            <a href="{{ route('slideshow.index') }}" class="slideshow-link">
                <h3>📺 Slideshow Standard</h3>
                <p>Versi all-in-one dengan CSS dan JS inline</p>
            </a>
            
            <a href="{{ route('slideshow.optimized') }}" class="slideshow-link">
                <h3>⚡ Slideshow Optimized</h3>
                <p>Versi optimized dengan file terpisah</p>
            </a>
        </div>

        <h3>🚀 Fitur Slideshow</h3>
        <div class="feature-list">
            <div class="feature-item">Keamanan tinggi</div>
            <div class="feature-item">Responsif</div>
            <div class="feature-item">Auto-refresh</div>
            <div class="feature-item">Multiple format</div>
            <div class="feature-item">Smooth transition</div>
            <div class="feature-item">Error handling</div>
            <div class="feature-item">Performance optimized</div>
            <div class="feature-item">Cache management</div>
        </div>

        <div class="info">
            <strong>Format yang didukung:</strong><br>
            📷 Gambar: JPG, PNG, GIF<br>
            🎬 Video: MP4, WebM
        </div>

        <div style="text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px;">
            <p>Dashboard Mading Digital - {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>