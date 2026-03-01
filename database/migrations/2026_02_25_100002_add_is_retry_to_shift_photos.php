<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_photos', function (Blueprint $table) {
            $table->boolean('is_retry')->default(false)->after('type')
                ->comment('Tekrar fotoğraf iste sonrası yüklenen fotoğraf');
        });
    }

    public function down(): void
    {
        Schema::table('shift_photos', function (Blueprint $table) {
            $table->dropColumn('is_retry');
        });
    }
};
