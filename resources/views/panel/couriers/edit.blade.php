@extends('layouts.panel')

@section('title', 'Kurye Düzenle')

@section('content')

<div class="mb-6">
    <a href="{{ route('panel.couriers.show', $courier) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('panel.couriers.update', $courier) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Kişisel Bilgiler -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Kişisel Bilgiler</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                    <input type="text" name="name" value="{{ old('name', $courier->name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                    <input type="email" name="email" value="{{ old('email', $courier->email) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $courier->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $courier->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
                </div>
            </div>
            
            <!-- İş Bilgileri -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">İş Bilgileri</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Çalışan Kodu</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $courier->employee_code) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Tipi</label>
                    <select name="vehicle_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seçiniz</option>
                        <option value="Motosiklet" {{ old('vehicle_type', $courier->vehicle_type) == 'Motosiklet' ? 'selected' : '' }}>Motosiklet</option>
                        <option value="Bisiklet" {{ old('vehicle_type', $courier->vehicle_type) == 'Bisiklet' ? 'selected' : '' }}>Bisiklet</option>
                        <option value="Elektrikli Scooter" {{ old('vehicle_type', $courier->vehicle_type) == 'Elektrikli Scooter' ? 'selected' : '' }}>Elektrikli Scooter</option>
                        <option value="Yaya" {{ old('vehicle_type', $courier->vehicle_type) == 'Yaya' ? 'selected' : '' }}>Yaya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Plakası</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $courier->vehicle_plate) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
        </div>
        
        <!-- Çalışma Bölgeleri -->
        <div class="mt-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Çalışma Bölgeleri *</h3>
            
            @php
                $currentDistricts = $courier->courierDistricts->pluck('id')->toArray();
                $primaryDistrict = $courier->courierDistricts->where('pivot.is_primary', true)->first();
            @endphp
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($districts as $district)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="district_ids[]" value="{{ $district->id }}"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               {{ in_array($district->id, old('district_ids', $currentDistricts)) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">{{ $district->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('district_ids')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ana Çalışma Bölgesi *</label>
                <select name="primary_district_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seçiniz</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" 
                                {{ old('primary_district_id', $primaryDistrict?->id) == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('panel.couriers.show', $courier) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                İptal
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Kaydet
            </button>
        </div>
        
    </form>
</div>

<!-- Şifre Sıfırlama -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Şifre Sıfırla</h3>
    
    <form method="POST" action="{{ route('panel.couriers.reset-password', $courier) }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yeni Şifre *</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
                    onclick="return confirm('Şifreyi sıfırlamak istediğinize emin misiniz? Kurye tüm oturumlardan çıkarılacaktır.')">
                Şifreyi Sıfırla
            </button>
        </div>
    </form>
</div>

@endsection
