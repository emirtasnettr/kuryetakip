<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\District;
use App\Models\Region;
use App\Rules\TurkishIdNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Panel Kurye Controller
 * 
 * Operasyon paneli için kurye yönetimi.
 */
class CourierController extends Controller
{
    /**
     * Kurye listesi
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->getAccessibleCouriers()->with(['courierDistricts', 'partner']);

        // Arama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Durum filtresi (varsayılan: aktif kuryeler)
        $status = $request->get('status', 'active');
        $query->where('is_active', $status === 'active');

        // İlçe filtresi
        if ($request->filled('district_id')) {
            $query->whereHas('courierDistricts', fn($q) => $q->where('district_id', $request->district_id));
        }

        // Sıralama
        $query->orderBy('name');

        $couriers = $query->with(['courierRegions', 'courierDistricts', 'partner'])
            ->paginate(20)
            ->withQueryString();
        $districts = District::active()->orderBy('name')->get();

        return view('panel.couriers.index', compact('couriers', 'districts'));
    }

    /**
     * Kurye listesini Excel (CSV) olarak indir
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $query = $user->getAccessibleCouriers()->with(['courierRegions', 'courierDistricts', 'partner']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('district_id')) {
            $query->whereHas('courierDistricts', fn ($q) => $q->where('district_id', $request->district_id));
        }

        $couriers = $query->orderBy('name')->get();

        $filename = 'kuryeler_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($couriers) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($stream, [
                'Ad Soyad',
                'E-posta',
                'Telefon',
                'T.C. Kimlik No',
                'Araç Tipi',
                'Plaka',
                'Bölge(ler)',
                'İlçe(ler)',
                'İş Ortağı',
                'Durum',
                'Son Giriş',
            ], ';');

            foreach ($couriers as $c) {
                $regions = $c->courierRegions->pluck('name')->join(', ');
                $districts = $c->courierDistricts->pluck('name')->join(', ');
                fputcsv($stream, [
                    $c->name,
                    $c->email,
                    $c->phone ?? '',
                    $c->employee_code ?? '',
                    $c->vehicle_type ?? '',
                    $c->vehicle_plate ?? '',
                    $regions,
                    $districts,
                    $c->partner?->name ?? '',
                    $c->is_active ? 'Aktif' : 'Pasif',
                    $c->last_login_at ? $c->last_login_at->format('d.m.Y H:i') : '',
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Kurye detayı
     */
    public function show(Request $request, User $courier)
    {
        $this->authorize('view', $courier);

        // Kurye kontrolü
        if (!$courier->isCourier()) {
            abort(404, 'Kurye bulunamadı.');
        }

        $courier->load(['courierDistricts', 'partner']);

        // Son vardiyalar
        $recentShifts = $courier->shifts()
            ->with(['district'])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        // Bu ay istatistikleri (yıl ve ay kontrolü)
        $monthlyStats = [
            'shift_count' => $courier->shifts()
                ->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->count(),
            'total_packages' => $courier->shifts()
                ->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->sum('package_count'),
            'total_hours' => round($courier->shifts()
                ->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->sum('total_minutes') / 60, 1),
        ];

        return view('panel.couriers.show', compact('courier', 'recentShifts', 'monthlyStats'));
    }

    /**
     * Yeni kurye formu
     */
    public function create()
    {
        $this->authorize('create', User::class);

        // Bölgeleri getir
        $regions = Region::active()->orderBy('name')->get();
        
        // İş ortağı listesi (sadece yöneticiler için)
        $partners = auth()->user()->isSystemAdmin() 
            ? User::withRole(Role::BUSINESS_PARTNER)->active()->get()
            : collect();

        return view('panel.couriers.create', compact('regions', 'partners'));
    }

    /**
     * Yeni kurye kaydet
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'required|string|max:20',
            'employee_code' => ['required', 'string', 'size:11', 'unique:users,employee_code', new TurkishIdNumber],
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'required|string|max:20',
            'region_ids' => 'required|array|min:1',
            'region_ids.*' => 'exists:regions,id',
            'partner_id' => 'nullable|exists:users,id',
        ], [
            'phone.required' => 'Telefon numarası zorunludur.',
            'employee_code.required' => 'T.C. Kimlik No zorunludur.',
            'employee_code.size' => 'T.C. Kimlik No 11 haneli olmalıdır.',
            'vehicle_plate.required' => 'Araç plakası zorunludur.',
            'region_ids.required' => 'En az bir bölge seçmelisiniz.',
        ]);

        // Kurye rolünü al
        $courierRole = Role::findByName(Role::COURIER);

        // İş ortağı kontrolü
        $partnerId = $validated['partner_id'] ?? null;
        if (auth()->user()->isBusinessPartner()) {
            $partnerId = auth()->id();
        }

        // Kurye oluştur (model'deki 'password' => 'hashed' cast tek sefer hash'ler)
        $courier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'role_id' => $courierRole->id,
            'partner_id' => $partnerId,
            'employee_code' => $validated['employee_code'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'is_active' => true,
        ]);

        // Bölgeleri ata
        $courier->courierRegions()->attach($validated['region_ids']);

        return redirect()->route('panel.couriers.show', $courier)
            ->with('success', 'Kurye başarıyla oluşturuldu.');
    }

    /**
     * Kurye düzenleme formu
     */
    public function edit(User $courier)
    {
        $this->authorize('update', $courier);

        if (!$courier->isCourier()) {
            abort(404);
        }

        $courier->load('courierRegions');
        $regions = Region::active()->orderBy('name')->get();
        
        $partners = auth()->user()->isSystemAdmin()
            ? User::withRole(Role::BUSINESS_PARTNER)->active()->get()
            : collect();

        return view('panel.couriers.edit', compact('courier', 'regions', 'partners'));
    }

    /**
     * Kurye güncelle
     */
    public function update(Request $request, User $courier)
    {
        $this->authorize('update', $courier);

        if (!$courier->isCourier()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $courier->id,
            'phone' => 'required|string|max:20',
            'employee_code' => ['required', 'string', 'size:11', 'unique:users,employee_code,' . $courier->id, new TurkishIdNumber],
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'required|string|max:20',
            'region_ids' => 'required|array|min:1',
            'region_ids.*' => 'exists:regions,id',
            'is_active' => 'boolean',
        ], [
            'phone.required' => 'Telefon numarası zorunludur.',
            'employee_code.required' => 'T.C. Kimlik No zorunludur.',
            'employee_code.size' => 'T.C. Kimlik No 11 haneli olmalıdır.',
            'vehicle_plate.required' => 'Araç plakası zorunludur.',
            'region_ids.required' => 'En az bir bölge seçmelisiniz.',
        ]);

        // Kurye bilgilerini güncelle
        $courier->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Bölgeleri güncelle
        $courier->courierRegions()->sync($validated['region_ids']);

        return redirect()->route('panel.couriers.show', $courier)
            ->with('success', 'Kurye başarıyla güncellendi.');
    }

    /**
     * Kurye aktif/pasif toggle
     */
    public function toggleActive(User $courier)
    {
        $this->authorize('toggleActive', $courier);

        if (!$courier->isCourier()) {
            abort(404);
        }

        // Aktif vardiyası varsa pasif yapılamaz
        if ($courier->is_active && $courier->hasActiveShift()) {
            return back()->with('error', 'Aktif vardiyası olan kurye pasif yapılamaz.');
        }

        $courier->update(['is_active' => !$courier->is_active]);

        $message = $courier->is_active 
            ? 'Kurye aktif hale getirildi.' 
            : 'Kurye pasif hale getirildi.';

        return back()->with('success', $message);
    }

    /**
     * Şifre sıfırlama
     */
    public function resetPassword(Request $request, User $courier)
    {
        $this->authorize('update', $courier);

        if (!$courier->isCourier()) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
        ]);

        // Doğrudan veritabanına tek hash ile yaz (model cast ile çift hash riski yok)
        DB::table('users')->where('id', $courier->id)->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Tüm token'ları sil (çıkış yaptır)
        $courier->tokens()->delete();

        return back()->with('success', 'Şifre başarıyla sıfırlandı.');
    }
}
