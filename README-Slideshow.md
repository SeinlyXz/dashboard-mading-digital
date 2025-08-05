# Digital Mading Slideshow

Tampilan slideshow media yang aman dan optimized untuk aplikasi Laravel Digital Mading.

## Fitur

- ✅ **Keamanan**: Rate limiting, validasi input, middleware keamanan
- ✅ **Responsif**: Tampilan optimal di berbagai ukuran layar
- ✅ **Auto-refresh**: Memperbarui daftar media setiap 60 detik
- ✅ **Multiple format**: Mendukung gambar (JPG, PNG, GIF) dan video (MP4, WebM)
- ✅ **Smooth transition**: Animasi transisi yang halus
- ✅ **Error handling**: Penanganan error yang baik
- ✅ **Performance**: Optimized loading dan caching

## Penggunaan

### URL Akses

1. **Versi Standard**: `/slideshow`
   - All-in-one file dengan CSS dan JS inline

2. **Versi Optimized**: `/slideshow/optimized`
   - CSS dan JS terpisah untuk performance lebih baik

### API Endpoint

- **GET** `/slideshow/media` - Mendapatkan daftar media (dengan cache)

## Konfigurasi

### Controller: `app/Http/Controllers/SlideshowController.php`

```php
// Mengatur format media yang didukung
->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'])

// Mengatur cache duration (default: 5 menit)
cache()->remember($cacheKey, 300, function () { ... })
```

### Middleware: `app/Http/Middleware/SlideshowSecurity.php`

```php
// Rate limiting (default: 120 request per menit)
$maxAttempts = 120;

// Cache duration untuk rate limiting
$decayMinutes = 1;
```

### JavaScript Configuration

```javascript
// Konfigurasi slideshow
new MediaSlideshow({
    refreshInterval: 60000,     // 60 detik
    slideDisplayTime: 5000,     // 5 detik per slide
    transitionDuration: 1500,   // 1.5 detik transisi
    maxRetries: 3,              // Max retry jika error
    retryDelay: 5000           // Delay antar retry
});
```

## Format Media yang Didukung

### Gambar
- JPG / JPEG
- PNG
- GIF (animasi didukung)

### Video
- MP4
- WebM

## Keamanan

1. **Rate Limiting**: Maksimal 120 request per menit per IP
2. **CSRF Protection**: Token CSRF untuk semua request
3. **Input Validation**: Validasi format dan tipe file
4. **Security Headers**: X-Frame-Options, X-XSS-Protection, dll
5. **Error Handling**: Tidak menampilkan informasi sensitif

## Database

Media diambil dari tabel `medias` dengan kriteria:
- `type` = 'image' atau 'video'
- `extension` dalam daftar format yang didukung
- Diurutkan berdasarkan `created_at` (terbaru)

## Performance

1. **Caching**: API response dicache 5 menit
2. **Preloading**: Gambar selanjutnya di-preload
3. **Lazy Loading**: Resource dimuat sesuai kebutuhan
4. **Optimized CSS/JS**: File terpisah untuk caching browser
5. **Memory Management**: Slide lama dihapus otomatis

## Troubleshooting

### Media tidak muncul
1. Cek apakah file media ada di storage
2. Pastikan kolom `url` atau `path` terisi dengan benar
3. Verifikasi permission file storage

### Slideshow tidak berjalan
1. Buka Developer Console untuk cek error JavaScript
2. Pastikan API `/slideshow/media` mengembalikan data
3. Cek koneksi internet untuk auto-refresh

### Performance issues
1. Gunakan versi optimized (`/slideshow/optimized`)
2. Kompres file media untuk mengurangi ukuran
3. Pertimbangkan menggunakan CDN untuk file media

## Development

### Menambah format media baru
1. Update validasi di `SlideshowController.php`
2. Tambahkan handler di `slideshow.js`
3. Update CSS jika diperlukan

### Mengubah timing
- Edit konfigurasi di `slideshow.js`
- Atau pass parameter ke constructor `MediaSlideshow`

### Custom styling
- Edit `public/css/slideshow.css`
- Atau override di view dengan CSS tambahan
