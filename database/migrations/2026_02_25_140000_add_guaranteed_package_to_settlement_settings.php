<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->boolean('has_guaranteed_package')->default(false)->after('vat_rate');
            $table->decimal('guaranteed_packages_per_hour', 8, 2)->nullable()->after('has_guaranteed_package');
            $table->unsignedInteger('max_guaranteed_packages_per_shift')->nullable()->after('guaranteed_packages_per_hour');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->dropColumn([
                'has_guaranteed_package',
                'guaranteed_packages_per_hour',
                'max_guaranteed_packages_per_shift',
            ]);
        });
    }
};
