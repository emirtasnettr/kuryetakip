<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vardiyalar Tablosu Migration
 * 
 * Kuryelerin vardiya kayıtlarını tutar.
 * Her vardiya başlangıç ve bitiş bilgilerini içerir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            
            // Kurye ilişkisi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // İlçe (vardiya hangi ilçede yapıldı)
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            
            // Vardiya durumu
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            
            // Başlangıç bilgileri
            $table->timestamp('started_at');                // Başlangıç zamanı
            $table->decimal('start_latitude', 10, 8)->nullable();  // Başlangıç enlemi
            $table->decimal('start_longitude', 11, 8)->nullable(); // Başlangıç boylamı
            $table->string('start_address')->nullable();    // Başlangıç adresi (reverse geocode)
            
            // Bitiş bilgileri
            $table->timestamp('ended_at')->nullable();      // Bitiş zamanı
            $table->decimal('end_latitude', 10, 8)->nullable();    // Bitiş enlemi
            $table->decimal('end_longitude', 11, 8)->nullable();   // Bitiş boylamı
            $table->string('end_address')->nullable();      // Bitiş adresi
            
            // İstatistikler
            $table->unsignedInteger('package_count')->nullable(); // Atılan paket sayısı
            $table->unsignedInteger('total_minutes')->nullable(); // Toplam çalışma dakikası
            
            // Meta bilgiler
            $table->text('notes')->nullable();              // Kurye notları
            $table->text('admin_notes')->nullable();        // Yönetici notları
            
            $table->timestamps();
            $table->softDeletes();
            
            // İndeksler
            $table->index('user_id');
            $table->index('district_id');
            $table->index('status');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index(['user_id', 'status']);           // Aktif vardiya sorguları için
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
