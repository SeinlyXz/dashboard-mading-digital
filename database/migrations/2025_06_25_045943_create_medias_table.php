<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->uuid('uuid')->unique(); // Unik ID tambahan (optional tapi sangat berguna)
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('url')->nullable(); // Bisa diisi jika menggunakan S3 atau CDN
            $table->string('type'); // Bisa "image", "video", "audio", dll
            $table->json('metadata')->nullable(); // Untuk simpan info tambahan seperti resolusi/durasi
            $table->timestamps();
            $table->softDeletes(); // Jika ingin bisa dihapus sementara
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
