@extends('layouts.panel')

@section('title', 'Yeni Kullanıcı')

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
        <h2 class="text-2xl font-bold text-gray-800">Yeni Kullanıcı Oluştur</h2>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('panel.users.store') }}" class="space-y-6">
        @csrf
        
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
                        value="{{ old('name') }}"
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
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Şifre *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar *</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        required
                        placeholder="05XX XXX XX XX"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('phone') border-red-500 @enderror"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="employee_code" class="block text-sm font-medium text-gray-700 mb-1">T.C. Kimlik No *</label>
                    <input 
                        type="text" 
                        id="tcKimlikNo" 
                        name="employee_code" 
                        value="{{ old('employee_code') }}"
                        required
                        maxlength="11"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="11 haneli T.C. Kimlik No"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black @error('employee_code') border-red-500 @enderror"
                    >
                    @error('employee_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="tcError" class="mt-1 text-sm text-red-600 hidden"></p>
                    <p id="tcSuccess" class="mt-1 text-sm text-green-600 hidden">Geçerli T.C. Kimlik No</p>
                    <p id="tcHint" class="mt-1 text-xs text-gray-500">11 haneli T.C. Kimlik numarası giriniz</p>
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
                        <option value="">Rol Seçin</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-name="{{ $role->name }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                            <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
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
                        <option value="Motosiklet" {{ old('vehicle_type') == 'Motosiklet' ? 'selected' : '' }}>Motosiklet</option>
                        <option value="Bisiklet" {{ old('vehicle_type') == 'Bisiklet' ? 'selected' : '' }}>Bisiklet</option>
                        <option value="Yaya" {{ old('vehicle_type') == 'Yaya' ? 'selected' : '' }}>Yaya</option>
                        <option value="Araç" {{ old('vehicle_type') == 'Araç' ? 'selected' : '' }}>Araç</option>
                    </select>
                </div>
                
                <div>
                    <label for="vehicle_plate" class="block text-sm font-medium text-gray-700 mb-1">Plaka</label>
                    <input 
                        type="text" 
                        id="vehicle_plate" 
                        name="vehicle_plate" 
                        value="{{ old('vehicle_plate') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black"
                    >
                </div>
            </div>
        </div>
        
        <!-- Bölge Ataması (Kurye için) -->
        <div id="courierDistrictFields" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Çalışma Bölgeleri</h3>
            
            <!-- Şehir Seçimi -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Çalışma Şehri *</label>
                <select id="courierCitySelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black">
                    <option value="">Şehir Seçiniz</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Sadece 1 şehir seçilebilir</p>
            </div>
            
            <!-- İlçe Seçimi -->
            <div id="courierDistrictsContainer" class="hidden mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma İlçeleri *</label>
                <div id="courierDistrictsGrid" class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach($districts as $district)
                        <label class="courier-district-item flex items-center p-2 hover:bg-gray-50 rounded hidden" data-city="{{ $district->city }}">
                            <input type="checkbox" name="districts[]" value="{{ $district->id }}"
                                   class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded courier-district-checkbox">
                            <span class="ml-2 text-sm text-gray-700">{{ $district->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Ana Bölge -->
            <div id="primaryDistrictField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ana Çalışma Bölgesi *</label>
                <select id="primary_district" name="primary_district"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black">
                    <option value="">Önce ilçe seçiniz</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" data-city="{{ $district->city }}" class="courier-primary-option hidden">
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Seçilen ilçelerden birini ana bölge olarak belirleyin</p>
            </div>
        </div>
        
        <!-- Bölge Ataması (Operasyon Personeli / İş Ortağı için) -->
        <div id="staffDistrictFields" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Yetkili Bölgeler</h3>
                <button type="button" onclick="addCityGroup()" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    İl Ekle
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Birden fazla il ve ilçe seçebilirsiniz.</p>
            
            <div id="cityGroupsContainer" class="space-y-4">
                <!-- City groups will be added here dynamically -->
            </div>
            
            <div id="noCityMessage" class="text-center py-8 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <p>Henüz il eklenmedi. "İl Ekle" butonuna tıklayın.</p>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('panel.users.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                İptal
            </a>
            <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                Kullanıcı Oluştur
            </button>
        </div>
    </form>
    
</div>

@push('scripts')
<script>
// İlçe verileri
const allDistricts = @json($districts->groupBy('city'));
const allCities = @json($cities);
let cityGroupCounter = 0;

function toggleRoleFields() {
    const roleSelect = document.getElementById('role_id');
    const selectedOption = roleSelect.options[roleSelect.selectedIndex];
    const roleName = selectedOption.dataset.name;
    
    const courierFields = document.getElementById('courierFields');
    const courierDistrictFields = document.getElementById('courierDistrictFields');
    const staffDistrictFields = document.getElementById('staffDistrictFields');
    const partnerField = document.getElementById('partnerField');
    
    // Reset
    courierFields.classList.add('hidden');
    courierDistrictFields.classList.add('hidden');
    staffDistrictFields.classList.add('hidden');
    partnerField.classList.add('hidden');
    
    if (roleName === 'courier') {
        courierFields.classList.remove('hidden');
        courierDistrictFields.classList.remove('hidden');
        partnerField.classList.remove('hidden');
    } else if (roleName === 'operation_specialist' || roleName === 'operation_manager' || roleName === 'business_partner') {
        staffDistrictFields.classList.remove('hidden');
    }
}

// Kurye için şehir değişikliği
document.getElementById('courierCitySelect')?.addEventListener('change', function() {
    const selectedCity = this.value;
    const container = document.getElementById('courierDistrictsContainer');
    const primaryField = document.getElementById('primaryDistrictField');
    const items = document.querySelectorAll('.courier-district-item');
    const primaryOptions = document.querySelectorAll('.courier-primary-option');
    
    if (!selectedCity) {
        container.classList.add('hidden');
        primaryField.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    primaryField.classList.remove('hidden');
    
    items.forEach(item => {
        const checkbox = item.querySelector('input');
        if (item.dataset.city === selectedCity) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
            checkbox.checked = false;
        }
    });
    
    primaryOptions.forEach(option => {
        if (option.dataset.city === selectedCity) {
            option.classList.remove('hidden');
        } else {
            option.classList.add('hidden');
        }
    });
    
    document.getElementById('primary_district').value = '';
    updateCourierPrimaryOptions();
});

document.querySelectorAll('.courier-district-checkbox').forEach(cb => {
    cb.addEventListener('change', updateCourierPrimaryOptions);
});

function updateCourierPrimaryOptions() {
    const selected = Array.from(document.querySelectorAll('.courier-district-checkbox:checked')).map(cb => cb.value);
    const primarySelect = document.getElementById('primary_district');
    
    document.querySelectorAll('.courier-primary-option').forEach(option => {
        if (!option.classList.contains('hidden') && option.value) {
            option.style.display = selected.includes(option.value) ? '' : 'none';
        }
    });
    
    if (!selected.includes(primarySelect.value)) {
        primarySelect.value = '';
    }
}

// Operasyon personeli için il grubu ekleme
function addCityGroup(preselectedCity = '', preselectedDistricts = []) {
    const container = document.getElementById('cityGroupsContainer');
    const noCityMessage = document.getElementById('noCityMessage');
    noCityMessage.classList.add('hidden');
    
    const groupId = cityGroupCounter++;
    
    // Zaten seçili şehirleri bul
    const usedCities = Array.from(container.querySelectorAll('.city-select')).map(s => s.value).filter(v => v);
    
    const div = document.createElement('div');
    div.className = 'city-group border border-gray-200 rounded-lg p-4';
    div.dataset.groupId = groupId;
    
    let cityOptions = '<option value="">Şehir Seçiniz</option>';
    allCities.forEach(city => {
        const disabled = usedCities.includes(city) && city !== preselectedCity ? 'disabled' : '';
        const selected = city === preselectedCity ? 'selected' : '';
        cityOptions += `<option value="${city}" ${disabled} ${selected}>${city}</option>`;
    });
    
    div.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <select class="city-select flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black" onchange="loadDistrictsForGroup(${groupId})">
                ${cityOptions}
            </select>
            <button type="button" onclick="removeCityGroup(${groupId})" class="ml-2 p-2 text-red-600 hover:bg-red-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
        <div class="districts-container hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">İlçeler</label>
            <div class="districts-grid grid grid-cols-2 md:grid-cols-3 gap-2 max-h-40 overflow-y-auto border border-gray-100 rounded-lg p-2 bg-gray-50"></div>
        </div>
    `;
    
    container.appendChild(div);
    
    if (preselectedCity) {
        loadDistrictsForGroup(groupId, preselectedDistricts);
    }
    
    updateCitySelectOptions();
}

function loadDistrictsForGroup(groupId, preselectedDistricts = []) {
    const group = document.querySelector(`.city-group[data-group-id="${groupId}"]`);
    const citySelect = group.querySelector('.city-select');
    const container = group.querySelector('.districts-container');
    const grid = group.querySelector('.districts-grid');
    const selectedCity = citySelect.value;
    
    if (!selectedCity) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    grid.innerHTML = '';
    
    const districts = allDistricts[selectedCity] || [];
    districts.forEach(district => {
        const checked = preselectedDistricts.includes(district.id) ? 'checked' : '';
        const label = document.createElement('label');
        label.className = 'flex items-center p-1.5 hover:bg-white rounded';
        label.innerHTML = `
            <input type="checkbox" name="districts[]" value="${district.id}" ${checked}
                   class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">${district.name}</span>
        `;
        grid.appendChild(label);
    });
    
    updateCitySelectOptions();
}

function removeCityGroup(groupId) {
    const group = document.querySelector(`.city-group[data-group-id="${groupId}"]`);
    group.remove();
    
    const container = document.getElementById('cityGroupsContainer');
    if (container.children.length === 0) {
        document.getElementById('noCityMessage').classList.remove('hidden');
    }
    
    updateCitySelectOptions();
}

function updateCitySelectOptions() {
    const container = document.getElementById('cityGroupsContainer');
    const usedCities = Array.from(container.querySelectorAll('.city-select')).map(s => s.value).filter(v => v);
    
    container.querySelectorAll('.city-select').forEach(select => {
        const currentValue = select.value;
        Array.from(select.options).forEach(option => {
            if (option.value && option.value !== currentValue) {
                option.disabled = usedCities.includes(option.value);
            }
        });
    });
}

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

tcInput?.addEventListener('input', updateTcValidation);
tcInput?.addEventListener('paste', function() {
    setTimeout(updateTcValidation, 0);
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    toggleRoleFields();
    if (tcInput?.value) {
        updateTcValidation();
    }
});
</script>
@endpush
@endsection
