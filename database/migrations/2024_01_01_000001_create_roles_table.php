<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roller Tablosu Migration
 * 
 * Sistem rolleri:
 * 1. courier - Kurye
 * 2. operation_specialist - Operasyon Uzmanı
 * 3. operation_manager - Operasyon Yöneticisi
 * 4. business_partner - İş Ortağı
 * 5. system_admin - Sistem Yöneticisi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();           // Rol adı (slug)
            $table->string('display_name', 100);            // Görünen ad
            $table->text('description')->nullable();        // Rol açıklaması
            $table->json('permissions')->nullable();        // JSON formatında yetkiler
            $table->boolean('is_active')->default(true);    // Aktif/Pasif durumu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
