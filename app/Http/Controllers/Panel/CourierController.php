<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\District;
use Illuminate\Http\Request;
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

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // İlçe filtresi
        if ($request->filled('district_id')) {
            $query->whereHas('courierDistricts', fn($q) => $q->where('district_id', $request->district_id));
        }

        // Sıralama
        $query->orderBy('name');

        $couriers = $query->paginate(20)->withQueryString();
        $districts = District::active()->orderBy('name')->get();

        return view('panel.couriers.index', compact('couriers', 'districts'));
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

        // Bu ay istatistikleri
        $monthlyStats = [
            'shift_count' => $courier->shifts()
                ->completed()
                ->whereMonth('started_at', now()->month)
                ->count(),
            'total_packages' => $courier->shifts()
                ->completed()
                ->whereMonth('started_at', now()->month)
                ->sum('package_count'),
            'total_hours' => round($courier->shifts()
                ->completed()
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

        $districts = District::active()->orderBy('name')->get();
        
        // İş ortağı listesi (sadece yöneticiler için)
        $partners = auth()->user()->isSystemAdmin() 
            ? User::withRole(Role::BUSINESS_PARTNER)->active()->get()
            : collect();

        return view('panel.couriers.create', compact('districts', 'partners'));
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
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'nullable|string|max:50|unique:users,employee_code',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
            'district_ids' => 'required|array|min:1',
            'district_ids.*' => 'exists:districts,id',
            'primary_district_id' => 'required|in_array:district_ids.*',
            'partner_id' => 'nullable|exists:users,id',
        ]);

        // Kurye rolünü al
        $courierRole = Role::findByName(Role::COURIER);

        // İş ortağı kontrolü
        $partnerId = $validated['partner_id'] ?? null;
        if (auth()->user()->isBusinessPartner()) {
            $partnerId = auth()->id();
        }

        // Kurye oluştur
        $courier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role_id' => $courierRole->id,
            'partner_id' => $partnerId,
            'employee_code' => $validated['employee_code'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'is_active' => true,
        ]);

        // İlçeleri ata
        foreach ($validated['district_ids'] as $districtId) {
            $courier->courierDistricts()->attach($districtId, [
                'is_primary' => $districtId == $validated['primary_district_id'],
                'assigned_by' => auth()->id(),
            ]);
        }

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

        $courier->load('courierDistricts');
        $districts = District::active()->orderBy('name')->get();
        
        $partners = auth()->user()->isSystemAdmin()
            ? User::withRole(Role::BUSINESS_PARTNER)->active()->get()
            : collect();

        return view('panel.couriers.edit', compact('courier', 'districts', 'partners'));
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
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'nullable|string|max:50|unique:users,employee_code,' . $courier->id,
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
            'district_ids' => 'required|array|min:1',
            'district_ids.*' => 'exists:districts,id',
            'primary_district_id' => 'required|in_array:district_ids.*',
            'is_active' => 'boolean',
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

        // İlçeleri güncelle
        $courier->courierDistricts()->detach();
        foreach ($validated['district_ids'] as $districtId) {
            $courier->courierDistricts()->attach($districtId, [
                'is_primary' => $districtId == $validated['primary_district_id'],
                'assigned_by' => auth()->id(),
            ]);
        }

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
        ]);

        $courier->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Tüm token'ları sil (çıkış yaptır)
        $courier->tokens()->delete();

        return back()->with('success', 'Şifre başarıyla sıfırlandı.');
    }
}
