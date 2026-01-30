<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * İlçeler Tablosu Migration
 * 
 * Kuryelerin ve operasyon uzmanlarının yetkili oldukları
 * ilçeleri tanımlar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                    // İlçe adı
            $table->string('city', 100)->default('İstanbul'); // Şehir adı
            $table->string('code', 10)->nullable();         // İlçe kodu (opsiyonel)
            $table->boolean('is_active')->default(true);    // Aktif/Pasif durumu
            $table->timestamps();
            
            // İlçe adı + şehir benzersiz olmalı
            $table->unique(['name', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
