<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Kullanıcı Yönetimi Controller
 * 
 * Sistem Yöneticisi tüm roller için kullanıcı oluşturabilir.
 */
class UserController extends Controller
{
    /**
     * Kullanıcı listesi
     */
    public function index(Request $request)
    {
        $this->authorize('manage-users');

        $query = User::with(['role', 'partner']);

        // Rol filtresi
        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Arama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::where('is_active', true)->get();

        return view('panel.users.index', compact('users', 'roles'));
    }

    /**
     * Kullanıcı oluşturma formu
     */
    public function create()
    {
        $this->authorize('manage-users');

        $roles = Role::where('is_active', true)->get();
        $districts = District::where('is_active', true)->orderBy('name')->get();
        $partners = User::whereHas('role', function ($q) {
            $q->where('name', Role::BUSINESS_PARTNER);
        })->where('is_active', true)->get();

        return view('panel.users.create', compact('roles', 'districts', 'partners'));
    }

    /**
     * Kullanıcı kaydet
     */
    public function store(Request $request)
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'nullable|string|max:50|unique:users,employee_code',
            'partner_id' => 'nullable|exists:users,id',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
            'primary_district' => 'nullable|exists:districts,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'partner_id' => $validated['partner_id'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Bölge ataması (kurye veya operasyon uzmanı için)
        if (!empty($validated['districts'])) {
            $role = Role::find($validated['role_id']);
            
            if ($role->name === Role::COURIER) {
                // Kurye için courier_districts tablosuna
                $districtData = [];
                foreach ($validated['districts'] as $districtId) {
                    $districtData[$districtId] = [
                        'is_primary' => $districtId == ($validated['primary_district'] ?? null),
                    ];
                }
                $user->courierDistricts()->attach($districtData);
            } else {
                // Operasyon personeli için user_districts tablosuna
                $user->authorizedDistricts()->attach($validated['districts']);
            }
        }

        return redirect()->route('panel.users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Kullanıcı detay
     */
    public function show(User $user)
    {
        $this->authorize('manage-users');

        $user->load(['role', 'partner', 'courierDistricts', 'authorizedDistricts']);

        return view('panel.users.show', compact('user'));
    }

    /**
     * Kullanıcı düzenleme formu
     */
    public function edit(User $user)
    {
        $this->authorize('manage-users');

        $roles = Role::where('is_active', true)->get();
        $districts = District::where('is_active', true)->orderBy('name')->get();
        $partners = User::whereHas('role', function ($q) {
            $q->where('name', Role::BUSINESS_PARTNER);
        })->where('is_active', true)->where('id', '!=', $user->id)->get();

        $user->load(['courierDistricts', 'authorizedDistricts']);

        return view('panel.users.edit', compact('user', 'roles', 'districts', 'partners'));
    }

    /**
     * Kullanıcı güncelle
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'partner_id' => 'nullable|exists:users,id',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
            'primary_district' => 'nullable|exists:districts,id',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'partner_id' => $validated['partner_id'] ?? null,
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Şifre güncelleme
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Bölge ataması güncelle
        $role = Role::find($validated['role_id']);
        
        // Önce mevcut atamaları temizle
        $user->courierDistricts()->detach();
        $user->authorizedDistricts()->detach();

        if (!empty($validated['districts'])) {
            if ($role->name === Role::COURIER) {
                $districtData = [];
                foreach ($validated['districts'] as $districtId) {
                    $districtData[$districtId] = [
                        'is_primary' => $districtId == ($validated['primary_district'] ?? null),
                    ];
                }
                $user->courierDistricts()->attach($districtData);
            } else {
                $user->authorizedDistricts()->attach($validated['districts']);
            }
        }

        return redirect()->route('panel.users.index')
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Kullanıcı sil
     */
    public function destroy(User $user)
    {
        $this->authorize('manage-users');

        // Kendini silemesin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendinizi silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('panel.users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }

    /**
     * Kullanıcı durumunu değiştir
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('manage-users');

        // Kendini pasifleştiremesin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendi hesabınızın durumunu değiştiremezsiniz.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Kullanıcı {$status} duruma getirildi.");
    }
}
