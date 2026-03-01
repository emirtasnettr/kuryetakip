<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planlı Vardiya - Bölge Pivot Tablosu
 * 
 * Bir vardiya birden fazla bölgeyi kapsayabilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_shift_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_shift_id')->constrained('scheduled_shifts')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->timestamps();
            
            // Aynı vardiyaya aynı bölge birden fazla eklenemez
            $table->unique(['scheduled_shift_id', 'district_id']);
        });

        // Mevcut verileri taşı (eğer varsa)
        // district_id kolonu hala var, verileri pivot tabloya kopyala
        DB::statement('
            INSERT INTO scheduled_shift_districts (scheduled_shift_id, district_id, created_at, updated_at)
            SELECT id, district_id, created_at, updated_at FROM scheduled_shifts WHERE district_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_shift_districts');
    }
};
