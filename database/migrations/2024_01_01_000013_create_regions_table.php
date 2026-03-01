<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bölgeler Tablosu
 * 
 * Sistem yöneticisinin tanımladığı özel bölgeler.
 * Her bölge birden fazla ilçe içerebilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Bölgeler tablosu
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // Bölge adı (örn: "Avrupa Yakası Kuzey")
            $table->string('color')->default('#3B82F6');         // Bölge rengi
            $table->text('description')->nullable();             // Açıklama
            $table->boolean('is_active')->default(true);         // Aktif mi?
            $table->timestamps();
            $table->softDeletes();
        });

        // Bölge - İlçe pivot tablosu
        Schema::create('region_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['region_id', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_districts');
        Schema::dropIfExists('regions');
    }
};
