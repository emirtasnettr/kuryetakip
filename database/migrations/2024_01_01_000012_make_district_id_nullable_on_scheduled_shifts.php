<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * district_id kolonunu nullable yap
 *
 * Çoklu bölge desteği için district_id artık zorunlu değil.
 * Bölgeler scheduled_shift_districts pivot tablosundan okunuyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE scheduled_shifts MODIFY district_id BIGINT UNSIGNED NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE scheduled_shifts ALTER COLUMN district_id DROP NOT NULL');
            return;
        }

        // SQLite: tablo yeniden oluştur (SQLite ALTER COLUMN desteklemez)
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');

            DB::statement('
                CREATE TABLE scheduled_shifts_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    district_id INTEGER NULL,
                    created_by INTEGER NOT NULL,
                    shift_date DATE NOT NULL,
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    required_couriers INTEGER NOT NULL,
                    status VARCHAR(255) DEFAULT "draft",
                    title VARCHAR(255) NULL,
                    notes TEXT NULL,
                    color VARCHAR(255) DEFAULT "#3B82F6",
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    deleted_at TIMESTAMP NULL,
                    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
                )
            ');

            DB::statement('INSERT INTO scheduled_shifts_new SELECT * FROM scheduled_shifts');
            DB::statement('DROP TABLE scheduled_shifts');
            DB::statement('ALTER TABLE scheduled_shifts_new RENAME TO scheduled_shifts');
            DB::statement('CREATE INDEX scheduled_shifts_district_id_index ON scheduled_shifts(district_id)');
            DB::statement('CREATE INDEX scheduled_shifts_shift_date_index ON scheduled_shifts(shift_date)');
            DB::statement('CREATE INDEX scheduled_shifts_status_index ON scheduled_shifts(status)');

            DB::statement('PRAGMA foreign_keys=on;');
        }
    }

    public function down(): void
    {
        // Geri almak için: district_id'yi tekrar NOT NULL yap (veri kaybı olabilir)
    }
};
