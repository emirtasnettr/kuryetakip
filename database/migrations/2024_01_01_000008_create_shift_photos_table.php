<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vardiya Fotoğrafları Tablosu Migration
 * 
 * Vardiya başlangıç ve bitiş fotoğraflarını tutar.
 * Hem local storage hem de S3 desteği için tasarlandı.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_photos', function (Blueprint $table) {
            $table->id();
            
            // Vardiya ilişkisi
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            
            // Fotoğraf tipi
            $table->enum('type', ['start', 'end'])->default('start');
            
            // Dosya bilgileri
            $table->string('filename');                     // Dosya adı
            $table->string('original_filename')->nullable(); // Orijinal dosya adı
            $table->string('path');                         // Dosya yolu
            $table->string('disk', 50)->default('public');  // Storage disk
            $table->string('mime_type', 100)->nullable();   // MIME tipi
            $table->unsignedBigInteger('size')->nullable(); // Dosya boyutu (byte)
            
            // Görsel özellikleri
            $table->unsignedInteger('width')->nullable();   // Genişlik (px)
            $table->unsignedInteger('height')->nullable();  // Yükseklik (px)
            
            // EXIF verileri (fotoğraf meta bilgileri)
            $table->decimal('exif_latitude', 10, 8)->nullable();  // EXIF'ten alınan enlem
            $table->decimal('exif_longitude', 11, 8)->nullable(); // EXIF'ten alınan boylam
            $table->timestamp('exif_taken_at')->nullable(); // Fotoğrafın çekildiği zaman
            
            $table->timestamps();
            
            // İndeksler
            $table->index('shift_id');
            $table->index('type');
            $table->index(['shift_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_photos');
    }
};
