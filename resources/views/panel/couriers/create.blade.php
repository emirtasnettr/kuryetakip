@extends('layouts.panel')

@section('title', 'Yeni Kurye')

@section('content')

<div class="mb-6">
    <a href="{{ route('panel.couriers.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <form method="POST" action="{{ route('panel.couriers.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Kişisel Bilgiler -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Kişisel Bilgiler</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <!-- İş Bilgileri -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">İş Bilgileri</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Çalışan Kodu</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Tipi</label>
                    <select name="vehicle_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seçiniz</option>
                        <option value="Motosiklet" {{ old('vehicle_type') == 'Motosiklet' ? 'selected' : '' }}>Motosiklet</option>
                        <option value="Bisiklet" {{ old('vehicle_type') == 'Bisiklet' ? 'selected' : '' }}>Bisiklet</option>
                        <option value="Elektrikli Scooter" {{ old('vehicle_type') == 'Elektrikli Scooter' ? 'selected' : '' }}>Elektrikli Scooter</option>
                        <option value="Yaya" {{ old('vehicle_type') == 'Yaya' ? 'selected' : '' }}>Yaya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Plakası</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                @if($partners->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">İş Ortağı</label>
                    <select name="partner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seçiniz (Opsiyonel)</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            
        </div>
        
        <!-- Çalışma Bölgeleri -->
        <div class="mt-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Çalışma Bölgeleri *</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($districts as $district)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="district_ids[]" value="{{ $district->id }}"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               {{ in_array($district->id, old('district_ids', [])) ? 'checked' : '' }}>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('primary_district_id') border-red-500 @enderror">
                    <option value="">Seçiniz</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ old('primary_district_id') == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
                @error('primary_district_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Submit -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('panel.couriers.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                İptal
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Kurye Oluştur
            </button>
        </div>
        
    </form>
</div>

@endsection
