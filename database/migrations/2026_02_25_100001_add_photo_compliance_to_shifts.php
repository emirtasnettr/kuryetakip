<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('photo_compliance_status', 30)->nullable()->after('admin_notes')
                ->comment('pending_review, bonus_approved, re_requested');
        });
        \DB::table('shifts')->where('status', 'completed')->whereNull('photo_compliance_status')->update(['photo_compliance_status' => 'pending_review']);
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('photo_compliance_status');
        });
    }
};
