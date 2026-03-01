<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('hourly_rate', 10, 2)->default(0)->comment('Saatlik ücret (TL)');
            $table->decimal('photo_compliance_bonus', 10, 2)->default(0)->comment('Vardiya uyumluluk primi (TL)');
            $table->timestamps();
        });

        \DB::table('settlement_settings')->insert([
            'hourly_rate' => 0,
            'photo_compliance_bonus' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_settings');
    }
};
