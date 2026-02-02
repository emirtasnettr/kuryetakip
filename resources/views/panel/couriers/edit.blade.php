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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                    <input type="text" name="phone" value="{{ old('phone', $courier->phone) }}" required
                           placeholder="05XX XXX XX XX"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">T.C. Kimlik No *</label>
                    <input type="text" name="employee_code" id="tcKimlikNo" value="{{ old('employee_code', $courier->employee_code) }}" required
                           maxlength="11" inputmode="numeric" pattern="[0-9]*"
                           placeholder="11 haneli T.C. Kimlik No"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('employee_code') border-red-500 @enderror">
                    @error('employee_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="tcError" class="mt-1 text-sm text-red-600 hidden"></p>
                    <p id="tcSuccess" class="mt-1 text-sm text-green-600 hidden">Geçerli T.C. Kimlik No</p>
                    <p id="tcHint" class="mt-1 text-xs text-gray-500">11 haneli T.C. Kimlik numarası giriniz</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Araç Plakası *</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $courier->vehicle_plate) }}" required
                           placeholder="34 ABC 123"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('vehicle_plate') border-red-500 @enderror">
                    @error('vehicle_plate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
        </div>
        
        <!-- Çalışma Bölgeleri -->
        <div class="mt-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Çalışma Bölgeleri *</h3>
            
            @php
                $currentDistricts = $courier->courierDistricts->pluck('id')->toArray();
                $primaryDistrict = $courier->courierDistricts->where('pivot.is_primary', true)->first();
                $selectedCity = old('city', $currentCity);
            @endphp
            
            <!-- Şehir Seçimi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Çalışma Şehri *</label>
                <select name="city" id="citySelect" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('city') border-red-500 @enderror">
                    <option value="">Şehir Seçiniz</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ $selectedCity == $city ? 'selected' : '' }}>
                            {{ $city }}
                        </option>
                    @endforeach
                </select>
                @error('city')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Sadece 1 şehir seçilebilir</p>
            </div>
            
            <!-- İlçe Seçimi -->
            <div id="districtsContainer" class="{{ $selectedCity ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma İlçeleri *</label>
                <p class="text-xs text-gray-500 mb-3">Birden fazla ilçe seçebilirsiniz</p>
                
                <div id="districtsGrid" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($districts as $district)
                        <label class="district-item flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $selectedCity == $district->city ? '' : 'hidden' }}" 
                               data-city="{{ $district->city }}">
                            <input type="checkbox" name="district_ids[]" value="{{ $district->id }}"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded district-checkbox"
                                   {{ in_array($district->id, old('district_ids', $currentDistricts)) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">{{ $district->name }}</span>
                        </label>
                    @endforeach
                </div>
                
                <div id="noDistrictsMessage" class="hidden p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                    Bu şehirde tanımlı ilçe bulunmuyor.
                </div>
            </div>
            @error('district_ids')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            
            <!-- Ana Bölge Seçimi -->
            <div id="primaryDistrictContainer" class="{{ $selectedCity ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ana Çalışma Bölgesi *</label>
                <select name="primary_district_id" id="primaryDistrictSelect" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('primary_district_id') border-red-500 @enderror">
                    <option value="">Önce ilçe seçiniz</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" 
                                data-city="{{ $district->city }}"
                                class="primary-option {{ $selectedCity == $district->city ? '' : 'hidden' }}"
                                {{ old('primary_district_id', $primaryDistrict?->id) == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
                @error('primary_district_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Seçilen ilçelerden birini ana bölge olarak belirleyin</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // T.C. Kimlik No Doğrulama
        const tcInput = document.getElementById('tcKimlikNo');
        const tcError = document.getElementById('tcError');
        const tcSuccess = document.getElementById('tcSuccess');
        const tcHint = document.getElementById('tcHint');
        
        function validateTcKimlikNo(tcno) {
            // Boşsa doğrulama yapma
            if (!tcno || tcno.length === 0) {
                return { valid: true, message: '' };
            }
            
            // Sadece rakam kontrolü
            if (!/^\d+$/.test(tcno)) {
                return { valid: false, message: 'T.C. Kimlik No sadece rakamlardan oluşmalıdır' };
            }
            
            // 11 haneden az ise henüz tamamlanmamış
            if (tcno.length < 11) {
                return { valid: false, message: 'T.C. Kimlik No 11 haneli olmalıdır (' + tcno.length + '/11)' };
            }
            
            // 11 haneden fazla
            if (tcno.length > 11) {
                return { valid: false, message: 'T.C. Kimlik No 11 haneli olmalıdır' };
            }
            
            // İlk hane 0 olamaz
            if (tcno[0] === '0') {
                return { valid: false, message: 'T.C. Kimlik No\'nun ilk hanesi 0 olamaz' };
            }
            
            // Matematiksel doğrulama
            const digits = tcno.split('').map(Number);
            
            // 10. hane kontrolü: ((1+3+5+7+9)*7 - (2+4+6+8)) % 10 = 10. hane
            const oddSum = digits[0] + digits[2] + digits[4] + digits[6] + digits[8];
            const evenSum = digits[1] + digits[3] + digits[5] + digits[7];
            let tenthDigit = ((oddSum * 7) - evenSum) % 10;
            if (tenthDigit < 0) tenthDigit += 10;
            
            if (tenthDigit !== digits[9]) {
                return { valid: false, message: 'Hatalı T.C. Kimlik No' };
            }
            
            // 11. hane kontrolü: (1+2+3+4+5+6+7+8+9+10) % 10 = 11. hane
            const sumFirst10 = digits.slice(0, 10).reduce((a, b) => a + b, 0);
            const eleventhDigit = sumFirst10 % 10;
            
            if (eleventhDigit !== digits[10]) {
                return { valid: false, message: 'Hatalı T.C. Kimlik No' };
            }
            
            return { valid: true, message: 'Geçerli T.C. Kimlik No' };
        }
        
        function updateTcValidation() {
            const value = tcInput.value.replace(/\D/g, ''); // Sadece rakamları al
            tcInput.value = value; // Rakam dışı karakterleri temizle
            
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
        
        // Sayfa yüklendiğinde mevcut değeri kontrol et
        if (tcInput.value) {
            updateTcValidation();
        }
        
        // Şehir/İlçe seçimi
        const citySelect = document.getElementById('citySelect');
        const districtsContainer = document.getElementById('districtsContainer');
        const primaryDistrictContainer = document.getElementById('primaryDistrictContainer');
        const districtItems = document.querySelectorAll('.district-item');
        const primaryOptions = document.querySelectorAll('.primary-option');
        const primaryDistrictSelect = document.getElementById('primaryDistrictSelect');
        const noDistrictsMessage = document.getElementById('noDistrictsMessage');
        
        // Şehir değiştiğinde
        citySelect.addEventListener('change', function() {
            const selectedCity = this.value;
            
            if (!selectedCity) {
                districtsContainer.classList.add('hidden');
                primaryDistrictContainer.classList.add('hidden');
                return;
            }
            
            districtsContainer.classList.remove('hidden');
            primaryDistrictContainer.classList.remove('hidden');
            
            // İlçeleri filtrele
            let visibleCount = 0;
            districtItems.forEach(item => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (item.dataset.city === selectedCity) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                    checkbox.checked = false; // Diğer şehirlerin seçimlerini temizle
                }
            });
            
            // İlçe yoksa mesaj göster
            if (visibleCount === 0) {
                noDistrictsMessage.classList.remove('hidden');
            } else {
                noDistrictsMessage.classList.add('hidden');
            }
            
            // Ana bölge seçeneklerini filtrele
            primaryOptions.forEach(option => {
                if (option.dataset.city === selectedCity) {
                    option.classList.remove('hidden');
                    option.disabled = false;
                } else {
                    option.classList.add('hidden');
                    option.disabled = true;
                }
            });
            
            // Şehir değiştiyse ana bölge seçimini sıfırla
            const currentPrimaryCity = primaryDistrictSelect.options[primaryDistrictSelect.selectedIndex]?.dataset?.city;
            if (currentPrimaryCity && currentPrimaryCity !== selectedCity) {
                primaryDistrictSelect.value = '';
            }
            
            updatePrimaryOptions();
        });
        
        // İlçe checkbox'ları değiştiğinde ana bölge seçeneklerini güncelle
        document.querySelectorAll('.district-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updatePrimaryOptions);
        });
        
        function updatePrimaryOptions() {
            const selectedDistrictIds = Array.from(document.querySelectorAll('.district-checkbox:checked'))
                .map(cb => cb.value);
            
            primaryOptions.forEach(option => {
                if (option.value && !option.classList.contains('hidden')) {
                    if (selectedDistrictIds.includes(option.value)) {
                        option.disabled = false;
                        option.style.display = '';
                    } else {
                        option.disabled = true;
                        option.style.display = 'none';
                    }
                }
            });
            
            // Eğer seçili ana bölge artık seçili ilçeler arasında değilse sıfırla
            if (!selectedDistrictIds.includes(primaryDistrictSelect.value)) {
                primaryDistrictSelect.value = '';
            }
        }
        
        // Sayfa yüklendiğinde mevcut durumu kontrol et
        updatePrimaryOptions();
    });
</script>
@endpush
