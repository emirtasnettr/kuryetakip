@extends('layouts.panel')

@section('title', 'Yeni Bölge Oluştur')

@push('styles')
<style>
    .city-dropdown {
        position: relative;
    }
    .city-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background: white;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 0.5rem 0.5rem;
        z-index: 50;
        display: none;
    }
    .city-dropdown-list.show {
        display: block;
    }
    .city-dropdown-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        transition: background-color 0.15s;
    }
    .city-dropdown-item:hover,
    .city-dropdown-item.highlighted {
        background-color: #f3f4f6;
    }
    .city-dropdown-item.selected {
        background-color: #dbeafe;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm">
        <!-- Header -->
        <div class="p-6 border-b">
            <div class="flex items-center gap-4">
                <a href="{{ route('panel.regions.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold">Yeni Bölge Oluştur</h1>
                    <p class="text-sm text-gray-500">Bölgeye bir isim, il ve renk verin</p>
                </div>
            </div>
        </div>

        <form action="{{ route('panel.regions.store') }}" method="POST">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- İl Seçimi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">İl *</label>
                    <div class="city-dropdown">
                        <input type="text" id="city-search" 
                               placeholder="İl ara veya seç..." 
                               autocomplete="off"
                               value="{{ old('city') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('city') border-red-500 @enderror">
                        <input type="hidden" name="city" id="city-value" value="{{ old('city') }}">
                        <div class="city-dropdown-list" id="city-list">
                            @foreach($cities as $city)
                                <div class="city-dropdown-item" data-value="{{ $city }}" data-search="{{ $city }}">
                                    {{ $city }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('city')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bölge Adı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bölge Adı *</label>
                    <p class="text-xs text-gray-500 mb-1">Türkçe karakter (ç, ğ, ı, ö, ş, ü vb.) kullanılamaz; sadece İngilizce harfler ve rakam.</p>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Örn: Avrupa Yakasi Kuzey"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Renk -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Renk</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" id="color-picker" value="{{ old('color', '#3B82F6') }}" 
                               class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer">
                        <input type="text" id="color-text" value="{{ old('color', '#3B82F6') }}" 
                               class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                               pattern="^#[0-9A-Fa-f]{6}$" placeholder="#3B82F6">
                        <div class="flex gap-2">
                            @foreach(['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'] as $preset)
                                <button type="button" onclick="setColor('{{ $preset }}')" 
                                        class="w-8 h-8 rounded-full border-2 border-transparent hover:border-gray-400 transition-colors"
                                        style="background-color: {{ $preset }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Açıklama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                    <textarea name="description" rows="3" 
                              placeholder="Bölge hakkında kısa bir açıklama (opsiyonel)..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t bg-gray-50 flex items-center justify-end gap-3">
                <a href="{{ route('panel.regions.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    İptal
                </a>
                <button type="submit" 
                        class="px-6 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800">
                    Bölge Oluştur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Renk seçici
    const colorPicker = document.getElementById('color-picker');
    const colorText = document.getElementById('color-text');

    colorPicker.addEventListener('input', function() {
        colorText.value = this.value.toUpperCase();
    });

    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorPicker.value = this.value;
        }
    });

    function setColor(color) {
        colorPicker.value = color;
        colorText.value = color;
    }

    // Türkçe karakter desteği ile küçük harfe çevir
    function turkishLowerCase(str) {
        return str.replace(/İ/g, 'i').replace(/I/g, 'ı').replace(/Ş/g, 'ş').replace(/Ğ/g, 'ğ')
                  .replace(/Ü/g, 'ü').replace(/Ö/g, 'ö').replace(/Ç/g, 'ç').toLowerCase();
    }

    // İl seçici
    const citySearch = document.getElementById('city-search');
    const cityValue = document.getElementById('city-value');
    const cityList = document.getElementById('city-list');
    const cityItems = document.querySelectorAll('.city-dropdown-item');
    let highlightedIndex = -1;

    // Input'a tıklayınca listeyi aç
    citySearch.addEventListener('focus', function() {
        cityList.classList.add('show');
        filterCities('');
    });

    // Input'a yazınca filtrele
    citySearch.addEventListener('input', function() {
        const search = turkishLowerCase(this.value);
        filterCities(search);
        highlightedIndex = -1;
        
        // Eğer tam eşleşme varsa seç
        const exactMatch = Array.from(cityItems).find(item => 
            turkishLowerCase(item.dataset.value) === search
        );
        if (exactMatch) {
            selectCity(exactMatch.dataset.value);
        } else {
            cityValue.value = '';
        }
    });

    // Klavye navigasyonu
    citySearch.addEventListener('keydown', function(e) {
        const visibleItems = Array.from(cityItems).filter(item => item.style.display !== 'none');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = Math.min(highlightedIndex + 1, visibleItems.length - 1);
            updateHighlight(visibleItems);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = Math.max(highlightedIndex - 1, 0);
            updateHighlight(visibleItems);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && visibleItems[highlightedIndex]) {
                selectCity(visibleItems[highlightedIndex].dataset.value);
                cityList.classList.remove('show');
            }
        } else if (e.key === 'Escape') {
            cityList.classList.remove('show');
        }
    });

    function updateHighlight(visibleItems) {
        cityItems.forEach(item => item.classList.remove('highlighted'));
        if (highlightedIndex >= 0 && visibleItems[highlightedIndex]) {
            visibleItems[highlightedIndex].classList.add('highlighted');
            visibleItems[highlightedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function filterCities(search) {
        cityItems.forEach(item => {
            const cityName = turkishLowerCase(item.dataset.search);
            if (cityName.includes(search)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectCity(value) {
        citySearch.value = value;
        cityValue.value = value;
        
        cityItems.forEach(item => {
            item.classList.remove('selected');
            if (item.dataset.value === value) {
                item.classList.add('selected');
            }
        });
    }

    // İl itemlerine tıklayınca seç
    cityItems.forEach(item => {
        item.addEventListener('click', function() {
            selectCity(this.dataset.value);
            cityList.classList.remove('show');
        });
    });

    // Dışarı tıklayınca listeyi kapat
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.city-dropdown')) {
            cityList.classList.remove('show');
        }
    });

    // Sayfa yüklendiğinde mevcut değeri işaretle
    if (cityValue.value) {
        selectCity(cityValue.value);
    }
</script>
@endpush
