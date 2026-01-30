<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kullanıcılar Tablosu Migration
 * 
 * Tüm sistem kullanıcıları (kurye, operasyon, yönetici vb.)
 * tek tabloda tutulur, rol ile ayrılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Temel bilgiler
            $table->string('name', 100);                    // Ad Soyad
            $table->string('email')->unique();              // E-posta (giriş için)
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();        // Telefon numarası
            
            // Rol ilişkisi
            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
            
            // İş ortağı ilişkisi (kurye için zorunlu olabilir)
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Kurye özel alanları
            $table->string('employee_code', 50)->nullable()->unique(); // Çalışan kodu
            $table->string('vehicle_type', 50)->nullable(); // Araç tipi (motor, bisiklet vb.)
            $table->string('vehicle_plate', 20)->nullable(); // Araç plakası
            
            // Durum ve meta
            $table->boolean('is_active')->default(true);    // Aktif/Pasif
            $table->timestamp('last_login_at')->nullable(); // Son giriş zamanı
            $table->string('last_login_ip', 45)->nullable(); // Son giriş IP
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();                          // Soft delete desteği
            
            // İndeksler
            $table->index('role_id');
            $table->index('partner_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
