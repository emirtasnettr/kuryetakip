<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vardiya Atamaları Tablosu Migration
 * 
 * Planlı vardiyalara atanan kuryeleri tutar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            
            // Planlı vardiya ilişkisi
            $table->foreignId('scheduled_shift_id')->constrained('scheduled_shifts')->onDelete('cascade');
            
            // Atanan kurye
            $table->foreignId('courier_id')->constrained('users')->onDelete('cascade');
            
            // Atamayı yapan kullanıcı
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            
            // Atama durumu
            $table->enum('status', ['assigned', 'confirmed', 'started', 'completed', 'cancelled', 'no_show'])->default('assigned');
            
            // Gerçek vardiya ile ilişkilendirme (kurye vardiyayı başlattığında)
            $table->foreignId('actual_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            
            // Meta bilgiler
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();       // Kuryenin onayladığı zaman
            $table->timestamp('started_at')->nullable();         // Kuryenin başlattığı zaman
            $table->timestamp('completed_at')->nullable();       // Bitiş zamanı
            
            $table->timestamps();
            
            // Aynı vardiyaya aynı kurye birden fazla atanamaz
            $table->unique(['scheduled_shift_id', 'courier_id']);
            
            // İndeksler
            $table->index('scheduled_shift_id');
            $table->index('courier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
