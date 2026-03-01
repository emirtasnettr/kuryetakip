<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kurye - Bölge Pivot Tablosu
 * 
 * Kuryeler artık ilçe yerine bölge bazlı çalışacak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Ana çalışma bölgesi
            $table->timestamps();
            
            $table->unique(['user_id', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_regions');
    }
};
