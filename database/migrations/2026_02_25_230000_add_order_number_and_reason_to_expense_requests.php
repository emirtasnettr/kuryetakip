<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->string('order_number', 128)->nullable()->after('receipt_photo_path')->comment('Sipariş numarası');
            $table->text('reason')->nullable()->after('order_number')->comment('Masraf nedeni');
        });
    }

    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'reason']);
        });
    }
};
