<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\MediaFile;
use App\Models\ExtraBonus;
use App\Models\SettlementDeduction;
use App\Models\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeAllData extends Command
{
    protected $signature = 'data:wipe {--no-admin : Admin kullanıcıyı da sil (sadece roller kalır)} {--force : Onay sormadan sil}';
    protected $description = 'Kuryeler, vardiya kayıtları, masraflar, bölgeler dahil tüm iş verilerini siler. Varsayılan: 1 admin hesabı kalır.';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Tüm kurye, vardiya, masraf ve bölge verileri silinecek. Devam?', true)) {
            return self::FAILURE;
        }

        $keepAdmin = !$this->option('no-admin');

        DB::beginTransaction();
        try {
            // 1) Vardiya ile ilgili – önce FK’ya bağlı tablolar, sonra ana tablolar (soft-delete dahil)
            DB::table('shift_photos')->delete();
            $this->info('shift_photos silindi.');
            DB::table('shift_logs')->delete();
            $this->info('shift_logs silindi.');
            // shift_assignments.actual_shift_id -> shifts.id, önce assignments silinsin
            DB::table('shift_assignments')->update(['actual_shift_id' => null]);
            DB::table('shift_assignments')->delete();
            $this->info('shift_assignments silindi.');
            DB::table('shifts')->delete();
            $this->info('shifts silindi (soft-deleted dahil).');
            DB::table('scheduled_shift_districts')->delete();
            DB::table('scheduled_shifts')->delete();
            $this->info('scheduled_shifts silindi.');

            // 2) Masraf ve medya
            ExpenseRequestItem::query()->delete();
            ExpenseRequest::query()->delete();
            $this->info('ExpenseRequest silindi.');
            MediaFile::query()->delete();
            $this->info('MediaFile silindi.');
            ExtraBonus::query()->delete();
            SettlementDeduction::query()->delete();
            $this->info('ExtraBonus / SettlementDeduction silindi.');

            // 3) Pivot tablolar (kurye-bölge, kurye-ilçe, kullanıcı-ilçe)
            DB::table('courier_regions')->delete();
            DB::table('courier_districts')->delete();
            DB::table('user_districts')->delete();
            $this->info('Pivot tablolar temizlendi.');

            // 4) Bölgeler (region_districts varsa onu da)
            if (DB::getSchemaBuilder()->hasTable('region_districts')) {
                DB::table('region_districts')->delete();
            }
            Region::withTrashed()->forceDelete();
            $this->info('Region silindi.');

            // 5) Tüm kullanıcıları sil (rolleri koru)
            $adminRoleId = Role::where('name', Role::SYSTEM_ADMIN)->value('id');
            $query = User::query();

            if ($keepAdmin && $adminRoleId) {
                $query->where('role_id', '!=', $adminRoleId);
            }

            $count = $query->count();
            $query->forceDelete();
            $this->info("{$count} kullanıcı silindi (kurye, yönetici, partner vb.).");

            DB::commit();
            $this->info('Tüm veriler silindi.');
            if ($keepAdmin) {
                $admin = User::where('role_id', $adminRoleId)->first();
                if ($admin) {
                    $this->info('Giriş için: ' . $admin->email . ' / password');
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Hata: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
