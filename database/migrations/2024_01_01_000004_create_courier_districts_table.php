<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kurye-İlçe Pivot Tablosu Migration
 * 
 * Kuryelerin hangi ilçelerde çalışabileceğini belirler.
 * Many-to-Many ilişkisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_districts', function (Blueprint $table) {
            $table->id();
            
            // İlişkiler
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            
            // Atama bilgileri
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_primary')->default(false);  // Ana çalışma bölgesi mi?
            
            $table->timestamps();
            
            // Her kurye bir ilçeye sadece bir kez atanabilir
            $table->unique(['user_id', 'district_id']);
            
            // İndeksler
            $table->index('user_id');
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_districts');
    }
};
