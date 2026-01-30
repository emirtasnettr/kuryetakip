@extends('layouts.panel')

@section('title', 'Kullanıcı Düzenle')

@section('content')
<div class="max-w-3xl mx-auto">
    
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('panel.users.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kullanıcılara Dön
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Kullanıcı Düzenle</h2>
        <p class="text-gray-500">{{ $user->name }}</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('panel.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Temel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Temel Bilgiler</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Yeni Şifre</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        placeholder="Değiştirmek istemiyorsanız boş bırakın"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $user->phone) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                </div>
                
                <div>
                    <label for="employee_code" class="block text-sm font-medium text-gray-700 mb-1">Sicil No</label>
                    <input 
                        type="text" 
                        id="employee_code" 
                        name="employee_code" 
                        value="{{ old('employee_code', $user->employee_code) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('employee_code') border-red-500 @enderror"
                    >
                    @error('employee_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Rol ve Yetki -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rol ve Yetki</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select 
                        id="role_id" 
                        name="role_id" 
                        required
                        onchange="toggleRoleFields()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('role_id') border-red-500 @enderror"
                    >
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-name="{{ $role->name }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div id="partnerField" class="hidden">
                    <label for="partner_id" class="block text-sm font-medium text-gray-700 mb-1">İş Ortağı</label>
                    <select 
                        id="partner_id" 
                        name="partner_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                        <option value="">İş Ortağı Seçin (Opsiyonel)</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id', $user->partner_id) == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Kullanıcı aktif</span>
                </label>
            </div>
        </div>
        
        <!-- Kurye Bilgileri -->
        <div id="courierFields" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kurye Bilgileri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-1">Araç Tipi</label>
                    <select 
                        id="vehicle_type" 
                        name="vehicle_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                        <option value="">Seçin</option>
                        <option value="Motosiklet" {{ old('vehicle_type', $user->vehicle_type) == 'Motosiklet' ? 'selected' : '' }}>Motosiklet</option>
                        <option value="Bisiklet" {{ old('vehicle_type', $user->vehicle_type) == 'Bisiklet' ? 'selected' : '' }}>Bisiklet</option>
                        <option value="Yaya" {{ old('vehicle_type', $user->vehicle_type) == 'Yaya' ? 'selected' : '' }}>Yaya</option>
                        <option value="Araç" {{ old('vehicle_type', $user->vehicle_type) == 'Araç' ? 'selected' : '' }}>Araç</option>
                    </select>
                </div>
                
                <div>
                    <label for="vehicle_plate" class="block text-sm font-medium text-gray-700 mb-1">Plaka</label>
                    <input 
                        type="text" 
                        id="vehicle_plate" 
                        name="vehicle_plate" 
                        value="{{ old('vehicle_plate', $user->vehicle_plate) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                </div>
            </div>
        </div>
        
        <!-- Bölge Ataması -->
        @php
            $userDistricts = $user->isCourier() 
                ? $user->courierDistricts->pluck('id')->toArray() 
                : $user->authorizedDistricts->pluck('id')->toArray();
            $primaryDistrictId = $user->courierDistricts->where('pivot.is_primary', true)->first()?->id;
        @endphp
        
        <div id="districtFields" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Bölge Ataması</h3>
            
            <div class="mb-4" id="primaryDistrictField">
                <label for="primary_district" class="block text-sm font-medium text-gray-700 mb-1">Ana Bölge</label>
                <select 
                    id="primary_district" 
                    name="primary_district"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                >
                    <option value="">Ana Bölge Seçin</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ $primaryDistrictId == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Yetkili Bölgeler</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach($districts as $district)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <input 
                                type="checkbox" 
                                name="districts[]" 
                                value="{{ $district->id }}"
                                {{ in_array($district->id, old('districts', $userDistricts)) ? 'checked' : '' }}
                                class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ $district->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('panel.users.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                İptal
            </a>
            <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                Değişiklikleri Kaydet
            </button>
        </div>
    </form>
    
    <!-- Silme Formu (Ana formun dışında) -->
    @if($user->id !== auth()->id())
        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('panel.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800 transition-colors">
                    Kullanıcıyı Sil
                </button>
            </form>
        </div>
    @endif
    
</div>

@push('scripts')
<script>
function toggleRoleFields() {
    const roleSelect = document.getElementById('role_id');
    const selectedOption = roleSelect.options[roleSelect.selectedIndex];
    const roleName = selectedOption.dataset.name;
    
    const courierFields = document.getElementById('courierFields');
    const districtFields = document.getElementById('districtFields');
    const partnerField = document.getElementById('partnerField');
    const primaryDistrictField = document.getElementById('primaryDistrictField');
    
    // Reset
    courierFields.classList.add('hidden');
    districtFields.classList.add('hidden');
    partnerField.classList.add('hidden');
    primaryDistrictField.classList.add('hidden');
    
    if (roleName === 'courier') {
        courierFields.classList.remove('hidden');
        districtFields.classList.remove('hidden');
        partnerField.classList.remove('hidden');
        primaryDistrictField.classList.remove('hidden');
    } else if (roleName === 'operation_specialist' || roleName === 'operation_manager') {
        districtFields.classList.remove('hidden');
    }
}

// Sayfa yüklendiğinde kontrol et
document.addEventListener('DOMContentLoaded', toggleRoleFields);
</script>
@endpush
@endsection
