<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Aynı isim-soyisim (name) ile kayıtlı test kuryeleri tekile indirir.
 * İlişkili vardiya, atama, ilçe/bölge verileri korunur (survivor kuryeye taşınır).
 */
class DeduplicateTestCouriers extends Command
{
    protected $signature = 'couriers:deduplicate-test';

    protected $description = 'Aynı isim-soyisime sahip test kuryeleri tekile düşürür (ilişkili veriler korunur)';

    public function handle(): int
    {
        $courierRole = Role::where('name', Role::COURIER)->first();
        if (!$courierRole) {
            $this->error('Kurye rolü bulunamadı.');
            return 1;
        }

        $testCouriers = User::query()
            ->where('role_id', $courierRole->id)
            ->where('email', 'like', '%@test.com')
            ->orderBy('id')
            ->get();

        $byName = $testCouriers->groupBy('name');
        $duplicates = $byName->filter(fn ($users) => $users->count() > 1);

        if ($duplicates->isEmpty()) {
            $this->info('Aynı isim-soyisimde birden fazla test kurye bulunamadı.');
            return 0;
        }

        $this->info('Aynı isim-soyisimde tekrarlanan test kurye grupları: ' . $duplicates->count());

        $merged = 0;
        foreach ($duplicates as $name => $users) {
            $survivor = $users->sortBy('id')->first();
            $toRemove = $users->where('id', '!=', $survivor->id);

            foreach ($toRemove as $duplicate) {
                $this->reassignToSurvivor($survivor, $duplicate);
                $duplicate->delete(); // Soft delete
                $merged++;
                $this->line("  → \"{$name}\": id {$duplicate->id} kaldırıldı, id {$survivor->id} korundu.");
            }
        }

        $this->newLine();
        $this->info("Toplam {$merged} tekrar eden kurye kaydı tekile düşürüldü.");
        return 0;
    }

    private function reassignToSurvivor(User $survivor, User $duplicate): void
    {
        // Vardiyalar
        \App\Models\Shift::where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);

        // Vardiya atamaları (kurye olarak atanan): survivor aynı vardiyada zaten varsa duplicate atamasını sil, yoksa survivor'a taşı
        $duplicateAssignments = \App\Models\ShiftAssignment::where('courier_id', $duplicate->id)->get();
        foreach ($duplicateAssignments as $assignment) {
            $exists = \App\Models\ShiftAssignment::where('scheduled_shift_id', $assignment->scheduled_shift_id)
                ->where('courier_id', $survivor->id)
                ->exists();
            if ($exists) {
                $assignment->delete();
            } else {
                $assignment->update(['courier_id' => $survivor->id]);
            }
        }
        \App\Models\ShiftAssignment::where('assigned_by', $duplicate->id)->update(['assigned_by' => null]);

        // İlçe atamaları: duplicate'ın ilçelerini survivor'a ekle, sonra duplicate'ı kaldır
        $duplicateDistricts = $duplicate->courierDistricts()->get();
        foreach ($duplicateDistricts as $district) {
            $pivot = $district->pivot;
            if (!$survivor->courierDistricts()->where('district_id', $district->id)->exists()) {
                $survivor->courierDistricts()->attach($district->id, [
                    'assigned_by' => $pivot->assigned_by,
                    'is_primary' => $pivot->is_primary,
                ]);
            }
        }
        $duplicate->courierDistricts()->detach();

        // Bölge atamaları
        $duplicateRegions = $duplicate->courierRegions()->get();
        foreach ($duplicateRegions as $region) {
            $pivot = $region->pivot;
            if (!$survivor->courierRegions()->where('region_id', $region->id)->exists()) {
                $survivor->courierRegions()->attach($region->id, [
                    'is_primary' => $pivot->is_primary ?? false,
                ]);
            }
        }
        $duplicate->courierRegions()->detach();

        // Yetkili ilçeler (user_districts)
        $duplicateDistrictsAuth = $duplicate->authorizedDistricts()->get();
        foreach ($duplicateDistrictsAuth as $district) {
            $pivot = $district->pivot;
            if (!$survivor->authorizedDistricts()->where('district_id', $district->id)->exists()) {
                $survivor->authorizedDistricts()->attach($district->id, [
                    'access_level' => $pivot->access_level ?? 'full',
                ]);
            }
        }
        $duplicate->authorizedDistricts()->detach();
    }
}
