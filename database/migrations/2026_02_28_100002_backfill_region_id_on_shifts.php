<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Atama üzerinden mevcut vardiyalara region_id doldurur.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('
                UPDATE shifts SET region_id = (
                    SELECT scheduled_shifts.region_id
                    FROM shift_assignments
                    JOIN scheduled_shifts ON scheduled_shifts.id = shift_assignments.scheduled_shift_id
                    WHERE shift_assignments.actual_shift_id = shifts.id
                    LIMIT 1
                )
                WHERE shifts.region_id IS NULL
                AND EXISTS (
                    SELECT 1 FROM shift_assignments WHERE shift_assignments.actual_shift_id = shifts.id
                )
            ');
        } else {
            DB::statement('
                UPDATE shifts s
                INNER JOIN shift_assignments sa ON sa.actual_shift_id = s.id
                INNER JOIN scheduled_shifts ss ON ss.id = sa.scheduled_shift_id
                SET s.region_id = ss.region_id
                WHERE s.region_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        // Geri almak için region_id'leri null yapmak isteyebilirsiniz; opsiyonel.
    }
};
