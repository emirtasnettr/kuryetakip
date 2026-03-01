<?php

namespace App\Console\Commands;

use App\Models\ShiftAssignment;
use App\Models\ScheduledShift;
use Illuminate\Console\Command;

class ClearScheduledShifts extends Command
{
    protected $signature = 'schedule:clear-shifts';
    protected $description = 'Tüm planlı vardiyaları ve atamalarını siler (ScheduledShift + ShiftAssignment).';

    public function handle(): int
    {
        $assignments = ShiftAssignment::count();
        $shifts = ScheduledShift::withTrashed()->count();

        ShiftAssignment::query()->delete();
        ScheduledShift::withTrashed()->forceDelete();

        $this->info("Silindi: {$assignments} atama, {$shifts} planlı vardiya.");
        return self::SUCCESS;
    }
}
