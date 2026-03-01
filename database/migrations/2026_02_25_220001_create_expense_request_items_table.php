<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_request_id')->constrained('expense_requests')->onDelete('cascade');
            $table->string('product_name');
            $table->string('quantity_or_kg', 64); // "2 adet", "1.5 kg" vb.
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('expense_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_request_items');
    }
};
