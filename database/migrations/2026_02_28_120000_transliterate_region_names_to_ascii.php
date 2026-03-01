<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Türkçe karakterleri ASCII karşılıklarına çevirir.
     */
    private function transliterate(string $value): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'C',
            'ğ' => 'g', 'Ğ' => 'G',
            'ı' => 'i', 'İ' => 'I', 'I' => 'I', // Türkçe ı -> i, İ -> I
            'ö' => 'o', 'Ö' => 'O',
            'ş' => 's', 'Ş' => 'S',
            'ü' => 'u', 'Ü' => 'U',
            'â' => 'a', 'Â' => 'A',
            'î' => 'i', 'Î' => 'I',
            'û' => 'u', 'Û' => 'U',
        ];
        return strtr($value, $map);
    }

    public function up(): void
    {
        $regions = DB::table('regions')->get();

        foreach ($regions as $region) {
            $newName = $this->transliterate($region->name);
            if ($newName !== $region->name) {
                DB::table('regions')->where('id', $region->id)->update(['name' => $newName]);
            }
        }
    }

    public function down(): void
    {
        // Geri alınamaz; orijinal isimler saklanmadı
    }
};
