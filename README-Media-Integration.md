# Media Model Integration

Dokumen ini menjelaskan integrasi model Media dengan slideshow page yang telah disesuaikan.

## Perubahan Yang Dilakukan

### 1. Model Media (app/Models/Media.php)
- **Struktur sederhana**: Hanya menggunakan kolom `path` sesuai dengan migrasi
- **Accessor properties**: Menambahkan computed properties untuk mendapatkan informasi file:
  - `getTypeAttribute()`: Menentukan tipe file (image/video) berdasarkan ekstensi
  - `getExtensionAttribute()`: Mengambil ekstensi file dari path
  - `getFilenameAttribute()`: Mengambil nama file dari path
  - `getMimeTypeAttribute()`: Menentukan MIME type berdasargi ekstensi
  - `getFullUrlAttribute()`: Menghasilkan URL lengkap untuk akses file

### 2. SlideshowController (app/Http/Controllers/SlideshowController.php)
- **Filtering dinamis**: Memfilter media berdasarkan:
  - File existence di storage
  - Tipe yang didukung (image/video)
  - Ekstensi yang valid (jpg, jpeg, png, gif, webp, mp4, webm)
- **Caching**: Menggunakan cache 5 menit untuk performa
- **Response format**: Mengembalikan data yang diperlukan slideshow

### 3. JavaScript Slideshow (public/js/slideshow.js)
- **Format data baru**: Menyesuaikan dengan struktur response dari API
- **Support WebP**: Menambahkan dukungan format WebP
- **Error handling**: Menangani kasus tidak ada media dengan lebih baik
- **Validasi**: Memvalidasi data media sebelum ditampilkan

### 4. Blade Template (resources/views/slideshow/optimized.blade.php)
- **Error state**: Menambahkan container untuk pesan error
- **User experience**: Memberikan feedback yang jelas saat tidak ada media

### 5. CSS (public/css/slideshow.css)
- **Error styling**: Menambahkan styling untuk pesan error
- **Responsive**: Memastikan tampilan error responsif

## Struktur Database

```sql
medias
├── id (Primary Key)
├── path (String - Path ke file di storage)
├── created_at
└── updated_at
```

## Format Response API

```json
[
  {
    "id": 1,
    "url": "http://domain.com/storage/path/to/file.jpg",
    "type": "image",
    "extension": "jpg",
    "filename": "file.jpg",
    "mime_type": "image/jpeg",
    "path": "path/to/file.jpg"
  }
]
```

## Tipe File Yang Didukung

### Images
- JPG/JPEG
- PNG
- GIF
- WebP

### Videos
- MP4
- WebM

## Cara Testing

1. **Jalankan migrasi**:
   ```bash
   php artisan migrate
   ```

2. **Seed data testing**:
   ```bash
   php artisan db:seed --class=MediaSeeder
   ```

3. **Buat symbolic link**:
   ```bash
   php artisan storage:link
   ```

4. **Upload file contoh** ke `storage/app/public/sample-images/` dan `storage/app/public/sample-videos/`

5. **Akses slideshow**:
   ```
   http://your-domain/
   ```

## Fitur Keamanan

- **Rate limiting**: 120 request per menit per IP
- **File validation**: Validasi ekstensi dan keberadaan file
- **Security headers**: X-Frame-Options, X-Content-Type-Options, dll
- **Error handling**: Penanganan error yang aman tanpa expose system info

## Performance

- **Caching**: API response di-cache 5 menit
- **Lazy loading**: Image loading dioptimalkan
- **Network detection**: Auto-reconnect saat koneksi pulih
- **Background refresh**: Auto-refresh data setiap 60 detik

## Maintenance

### Membersihkan Cache
```bash
php artisan cache:clear
```

### Regenerate Storage Link
```bash
php artisan storage:link
```

### Monitoring File Storage
Model Media menyediakan method untuk memeriksa dan membersihkan file:
- `$media->fileExists()`: Cek apakah file masih ada
- `$media->deleteFileFromStorage()`: Hapus file dari storage
- `$media->deleteWithFile()`: Hapus record dan file sekaligus
