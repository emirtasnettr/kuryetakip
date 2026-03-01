<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->comment('KDV dahil tutar (TL)');
            $table->text('reason')->comment('Prim nedeni');
            $table->date('bonus_date')->comment('Hakedişe yansıyacağı tarih');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_bonuses');
    }
};
