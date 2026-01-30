<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vardiya Logları Tablosu Migration
 * 
 * Vardiya başlangıç ve bitiş işlemlerinin detaylı loglarını tutar.
 * Güvenlik ve denetim amaçlı tüm bilgiler kaydedilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_logs', function (Blueprint $table) {
            $table->id();
            
            // Vardiya ilişkisi
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            
            // Log tipi
            $table->enum('type', ['start', 'end', 'pause', 'resume', 'update'])->default('start');
            
            // Konum bilgileri
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('accuracy')->nullable(); // GPS doğruluğu (metre)
            
            // Cihaz bilgileri
            $table->string('ip_address', 45)->nullable();   // IPv4 veya IPv6
            $table->string('user_agent')->nullable();       // Tarayıcı/Uygulama bilgisi
            $table->string('device_id', 100)->nullable();   // Cihaz kimliği
            $table->string('device_model', 100)->nullable(); // Cihaz modeli
            $table->string('os_version', 50)->nullable();   // İşletim sistemi versiyonu
            $table->string('app_version', 20)->nullable();  // Uygulama versiyonu
            
            // Ek veriler
            $table->json('metadata')->nullable();           // Ek JSON veriler
            
            $table->timestamp('logged_at');                 // Log zamanı
            $table->timestamps();
            
            // İndeksler
            $table->index('shift_id');
            $table->index('type');
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_logs');
    }
};
