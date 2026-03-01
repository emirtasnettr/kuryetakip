@extends('layouts.panel')

@section('title', 'Yeni Kurye')

@section('content')

<div class="mb-6">
    <a href="{{ route('panel.couriers.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center">
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
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           placeholder="05XX XXX XX XX"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black">
                </div>
            </div>
            
            <!-- İş Bilgileri -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">İş Bilgileri</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">T.C. Kimlik No *</label>
                    <input type="text" name="employee_code" id="tcKimlikNo" value="{{ old('employee_code') }}" required
                           maxlength="11" inputmode="numeric" pattern="[0-9]*"
                           placeholder="11 haneli T.C. Kimlik No"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('employee_code') border-red-500 @enderror">
                    @error('employee_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="tcError" class="mt-1 text-sm text-red-600 hidden"></p>
                    <p id="tcSuccess" class="mt-1 text-sm text-green-600 hidden">Geçerli T.C. Kimlik No</p>
                    <p id="tcHint" class="mt-1 text-xs text-gray-500">11 haneli T.C. Kimlik numarası giriniz</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Tipi</label>
                    <select name="vehicle_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black">
                        <option value="">Seçiniz</option>
                        <option value="Motosiklet" {{ old('vehicle_type') == 'Motosiklet' ? 'selected' : '' }}>Motosiklet</option>
                        <option value="Bisiklet" {{ old('vehicle_type') == 'Bisiklet' ? 'selected' : '' }}>Bisiklet</option>
                        <option value="Elektrikli Scooter" {{ old('vehicle_type') == 'Elektrikli Scooter' ? 'selected' : '' }}>Elektrikli Scooter</option>
                        <option value="Yaya" {{ old('vehicle_type') == 'Yaya' ? 'selected' : '' }}>Yaya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Plakası *</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" required
                           placeholder="34 ABC 123"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('vehicle_plate') border-red-500 @enderror">
                    @error('vehicle_plate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                @if($partners->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">İş Ortağı</label>
                    <select name="partner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black">
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
            
            @if($regions->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800">
                        Henüz bölge tanımlanmamış. 
                        <a href="{{ route('panel.regions.create') }}" class="underline font-medium">Önce bölge oluşturun</a>.
                    </p>
                </div>
            @else
                <!-- Bölge Seçimi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Çalışacağı Bölgeler *</label>
                    <p class="text-xs text-gray-500 mb-3">Birden fazla bölge seçebilirsiniz</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($regions as $region)
                            <label class="region-item flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors
                                          {{ in_array($region->id, old('region_ids', [])) ? 'bg-green-50 border-green-300' : '' }}">
                                <input type="checkbox" name="region_ids[]" value="{{ $region->id }}"
                                       class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded region-checkbox"
                                       {{ in_array($region->id, old('region_ids', [])) ? 'checked' : '' }}>
                                <div class="ml-3 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $region->color }}"></span>
                                    <span class="font-medium text-gray-900">{{ $region->name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('region_ids')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
            @endif
        </div>
        
        <!-- Submit -->
        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('panel.couriers.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                İptal
            </a>
            <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800" {{ $regions->isEmpty() ? 'disabled' : '' }}>
                Kurye Oluştur
            </button>
        </div>
        
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // T.C. Kimlik No Doğrulama
        const tcInput = document.getElementById('tcKimlikNo');
        const tcError = document.getElementById('tcError');
        const tcSuccess = document.getElementById('tcSuccess');
        const tcHint = document.getElementById('tcHint');
        
        function validateTcKimlikNo(tcno) {
            if (!tcno || tcno.length === 0) {
                return { valid: true, message: '' };
            }
            
            if (!/^\d+$/.test(tcno)) {
                return { valid: false, message: 'T.C. Kimlik No sadece rakamlardan oluşmalıdır' };
            }
            
            if (tcno.length < 11) {
                return { valid: false, message: 'T.C. Kimlik No 11 haneli olmalıdır (' + tcno.length + '/11)' };
            }
            
            if (tcno.length > 11) {
                return { valid: false, message: 'T.C. Kimlik No 11 haneli olmalıdır' };
            }
            
            if (tcno[0] === '0') {
                return { valid: false, message: 'T.C. Kimlik No\'nun ilk hanesi 0 olamaz' };
            }
            
            const digits = tcno.split('').map(Number);
            
            const oddSum = digits[0] + digits[2] + digits[4] + digits[6] + digits[8];
            const evenSum = digits[1] + digits[3] + digits[5] + digits[7];
            let tenthDigit = ((oddSum * 7) - evenSum) % 10;
            if (tenthDigit < 0) tenthDigit += 10;
            
            if (tenthDigit !== digits[9]) {
                return { valid: false, message: 'Hatalı T.C. Kimlik No' };
            }
            
            const sumFirst10 = digits.slice(0, 10).reduce((a, b) => a + b, 0);
            const eleventhDigit = sumFirst10 % 10;
            
            if (eleventhDigit !== digits[10]) {
                return { valid: false, message: 'Hatalı T.C. Kimlik No' };
            }
            
            return { valid: true, message: 'Geçerli T.C. Kimlik No' };
        }
        
        function updateTcValidation() {
            const value = tcInput.value.replace(/\D/g, '');
            tcInput.value = value;
            
            const result = validateTcKimlikNo(value);
            
            if (value.length === 0) {
                tcError.classList.add('hidden');
                tcSuccess.classList.add('hidden');
                tcHint.classList.remove('hidden');
                tcInput.classList.remove('border-red-500', 'border-green-500');
                tcInput.classList.add('border-gray-300');
            } else if (result.valid && value.length === 11) {
                tcError.classList.add('hidden');
                tcSuccess.classList.remove('hidden');
                tcHint.classList.add('hidden');
                tcInput.classList.remove('border-red-500', 'border-gray-300');
                tcInput.classList.add('border-green-500');
            } else {
                tcError.textContent = result.message;
                tcError.classList.remove('hidden');
                tcSuccess.classList.add('hidden');
                tcHint.classList.add('hidden');
                tcInput.classList.remove('border-green-500', 'border-gray-300');
                tcInput.classList.add('border-red-500');
            }
        }
        
        tcInput.addEventListener('input', updateTcValidation);
        tcInput.addEventListener('paste', function() {
            setTimeout(updateTcValidation, 0);
        });
        
        if (tcInput.value) {
            updateTcValidation();
        }
        
        // Bölge seçimi
        const regionCheckboxes = document.querySelectorAll('.region-checkbox');
        
        function updateRegionStyles() {
            document.querySelectorAll('.region-item').forEach(item => {
                const checkbox = item.querySelector('.region-checkbox');
                if (checkbox.checked) {
                    item.classList.add('bg-green-50', 'border-green-300');
                } else {
                    item.classList.remove('bg-green-50', 'border-green-300');
                }
            });
        }
        
        regionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateRegionStyles);
        });
    });
</script>
@endpush
