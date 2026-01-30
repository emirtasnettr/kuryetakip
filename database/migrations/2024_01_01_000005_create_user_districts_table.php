<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kullanıcı-İlçe Pivot Tablosu Migration
 * 
 * Operasyon uzmanlarının yetkili oldukları ilçeleri belirler.
 * Kurye dışındaki kullanıcılar için kullanılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_districts', function (Blueprint $table) {
            $table->id();
            
            // İlişkiler
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            
            // Yetki seviyesi
            $table->enum('access_level', ['view', 'manage', 'full'])->default('view');
            
            $table->timestamps();
            
            // Her kullanıcı bir ilçeye sadece bir kez atanabilir
            $table->unique(['user_id', 'district_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_districts');
    }
};
