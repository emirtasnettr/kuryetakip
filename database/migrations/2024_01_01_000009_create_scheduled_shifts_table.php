<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planlı Vardiyalar Tablosu Migration
 * 
 * Sistem yöneticisinin önceden planladığı vardiyaları tutar.
 * Bölge bazlı, kapasite belirlenmiş vardiyalar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_shifts', function (Blueprint $table) {
            $table->id();
            
            // İlçe/Bölge ilişkisi
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            
            // Oluşturan yönetici
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Vardiya tarihi ve saatleri
            $table->date('shift_date');                          // Vardiya tarihi
            $table->time('start_time');                          // Başlangıç saati
            $table->time('end_time');                            // Bitiş saati
            
            // Kapasite
            $table->unsignedInteger('required_couriers');        // Gereken kurye sayısı
            
            // Durum
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            
            // Meta bilgiler
            $table->string('title')->nullable();                 // Vardiya başlığı (opsiyonel)
            $table->text('notes')->nullable();                   // Notlar
            $table->string('color')->default('#3B82F6');         // Takvimde gösterilecek renk
            
            $table->timestamps();
            $table->softDeletes();
            
            // İndeksler
            $table->index('district_id');
            $table->index('shift_date');
            $table->index('status');
            $table->index(['district_id', 'shift_date']);
            $table->index(['shift_date', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_shifts');
    }
};
