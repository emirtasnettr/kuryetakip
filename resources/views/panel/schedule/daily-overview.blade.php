@extends('layouts.panel')

@section('title', 'Canlı Operasyon')

@section('content')
<div class="space-y-6">
    <!-- Tarih Seçimi ve Özet -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Tarih Navigasyonu -->
            <div class="flex items-center gap-3">
                <a href="{{ route('panel.shift-overview', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" 
                   class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                
                <div class="text-center">
                    <h2 class="text-2xl font-bold">{{ $date->translatedFormat('d F Y') }}</h2>
                    <p class="text-gray-500">{{ $date->translatedFormat('l') }}</p>
                </div>
                
                <a href="{{ route('panel.shift-overview', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" 
                   class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                
                @if(!$date->isToday())
                    <a href="{{ route('panel.shift-overview') }}" class="ml-2 px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                        Bugün
                    </a>
                @else
                    <span class="ml-2 px-3 py-1 text-sm bg-green-100 text-green-700 rounded-lg">
                        Bugün
                    </span>
                @endif
            </div>
            
            <!-- Tarih Seçici -->
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-black focus:border-black"
                       onchange="this.form.submit()">
            </form>
        </div>
        
        <!-- Özet İstatistikler -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 md:gap-4 mt-6">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-gray-900">{{ $stats['total_shifts'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Toplam Vardiya</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-blue-600">{{ $stats['regions_with_shifts'] }}/{{ $stats['total_regions'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Aktif Bölge</p>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-indigo-600">{{ $stats['couriers_with_shift_count'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Vardiyası Olan Kurye Sayısı</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-green-600">{{ $stats['couriers_on_shift_count'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Vardiyada Olan Kurye Sayısı</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-orange-600">{{ $stats['late_entry_count'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Vardiyaya Geç Giren Kurye Sayısı</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <p class="text-2xl md:text-3xl font-bold text-red-600">{{ $stats['no_show_count'] }}</p>
                <p class="text-xs md:text-sm text-gray-500">Vardiyaya Girmeyen Kurye Sayısı</p>
            </div>
            <div class="{{ $stats['compliance_pct'] >= 90 ? 'bg-green-50' : ($stats['compliance_pct'] >= 70 ? 'bg-amber-50' : 'bg-red-50') }} rounded-lg p-4 text-center" title="Planlanan toplam vardiya saatine sağlanan uyum (başlatılan vardiya süreleri / toplam gerekli süre)">
                <p class="text-2xl md:text-3xl font-bold {{ $stats['compliance_pct'] >= 90 ? 'text-green-600' : ($stats['compliance_pct'] >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $stats['compliance_pct'] }}%
                </p>
                <p class="text-xs md:text-sm text-gray-500">Uygunluk Oranı</p>
            </div>
        </div>
    </div>
    
    <!-- Tüm Bölgeler Grid -->
    @if($shifts->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($regions as $region)
                @php
                    $regionShifts = $shiftsByRegion->get($region->id, collect());
                    $regionRequired = $regionShifts->sum('required_couriers');
                    $regionAssigned = $regionShifts->sum(fn($s) => $s->validAssignments->count());
                @endphp
                
                @php
                    $isRegionComplete = $regionShifts->isNotEmpty() && $regionAssigned >= $regionRequired;
                    $hasShifts = $regionShifts->isNotEmpty();
                @endphp
                <div class="rounded-xl overflow-hidden border-2 {{ $regionShifts->isEmpty() ? 'opacity-50 bg-white border-gray-200' : ($isRegionComplete ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400') }}">
                    <!-- Bölge Başlığı -->
                    <div class="px-4 py-3 flex items-center justify-between border-b {{ $isRegionComplete ? 'border-green-200' : ($hasShifts ? 'border-red-200' : 'border-gray-200') }}">
                        <div class="flex items-center gap-2">
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $region->name }}</h3>
                                @if($region->city)
                                    <p class="text-xs text-gray-500">{{ $region->city }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if($regionShifts->isNotEmpty())
                                <p class="text-lg font-bold {{ $regionAssigned >= $regionRequired ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $regionAssigned }}/{{ $regionRequired }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $regionShifts->count() }} vardiya</p>
                            @else
                                <p class="text-sm text-gray-400">Vardiya yok</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Vardiyalar -->
                    @if($regionShifts->isNotEmpty())
                        <div class="divide-y {{ $isRegionComplete ? 'divide-green-200' : 'divide-red-200' }} max-h-96 overflow-y-auto">
                            @foreach($regionShifts as $shift)
                                @php
                                    $shiftAssignedCount = $shift->validAssignments->count();
                                    $isShiftComplete = $shiftAssignedCount >= $shift->required_couriers;
                                    $isShiftEnded = $shift->status === 'completed';
                                @endphp
                                <div class="p-3 cursor-pointer transition-colors {{ $isShiftEnded ? 'hover:bg-gray-100 bg-gray-50' : ($isShiftComplete ? 'hover:bg-green-100' : 'hover:bg-red-100') }}" onclick="openCourierModal({{ $shift->id }})">
                                    <!-- Vardiya Saati ve Bilgisi -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </span>
                                            @if($shift->title)
                                                <span class="text-xs text-gray-500">{{ $shift->title }}</span>
                                            @endif
                                            @if($isShiftEnded)
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-gray-300 text-gray-700">
                                                    Tamamlandı
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $isShiftEnded ? 'bg-gray-200 text-gray-700' : ($isShiftComplete ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') }}">
                                                {{ $shiftAssignedCount }}/{{ $shift->required_couriers }}
                                            </span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Atanmış Kuryeler (tamamlanmış vardiyalar dahil tüm atamalar) -->
                                    @if($shift->validAssignments->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($shift->validAssignments as $assignment)
                                                @php
                                                    // Atanmış / Vardiyayı başlattı / Geç başladı / Girmemiş
                                                    $isLate = false;
                                                    $notStarted = false;
                                                    $hasStarted = (bool) $assignment->started_at;
                                                    $startTimeFormatted = \Carbon\Carbon::parse($shift->start_time)->format('H:i:s');
                                                    $scheduledStart = \Carbon\Carbon::parse($shift->shift_date->format('Y-m-d') . ' ' . $startTimeFormatted);
                                                    
                                                    if ($hasStarted) {
                                                        $actualStart = \Carbon\Carbon::parse($assignment->started_at);
                                                        $isLate = $actualStart->gt($scheduledStart->copy()->addMinutes(5));
                                                    } else {
                                                        $now = \Carbon\Carbon::now();
                                                        $notStarted = $now->gt($scheduledStart->copy()->addMinutes(5));
                                                    }
                                                    $statusTitle = $notStarted ? 'Vardiyaya girmemiş' : ($isLate ? 'Geç başladı' : ($hasStarted ? 'Vardiyayı başlattı' : 'Atanmış'));
                                                    $bgClass = $notStarted ? 'bg-red-100 text-red-800' : ($isLate ? 'bg-orange-100 text-orange-800' : ($hasStarted ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'));
                                                    $avatarClass = $notStarted ? 'bg-red-600' : ($isLate ? 'bg-orange-600' : ($hasStarted ? 'bg-blue-600' : 'bg-green-600'));
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs {{ $bgClass }} rounded-full" title="{{ $assignment->courier->phone }} · {{ $statusTitle }}">
                                                    <span class="w-5 h-5 {{ $avatarClass }} text-white rounded-full flex items-center justify-center text-[10px] font-medium">
                                                        {{ strtoupper(substr($assignment->courier->name, 0, 1)) }}
                                                    </span>
                                                    {{ Str::before($assignment->courier->name, ' ') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-red-600 font-medium flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Kurye atanmamış - Tıklayın
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-gray-400 text-sm">
                            Bu gün için vardiya planlanmamış
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-lg font-medium text-gray-500 mb-2">Bu gün için vardiya bulunamadı</p>
            <p class="text-sm text-gray-400 mb-4">{{ $date->translatedFormat('d F Y, l') }}</p>
            <a href="{{ route('panel.schedule.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Vardiya Planlamaya Git
            </a>
        </div>
    @endif
</div>

<!-- Kurye Yönetim Modal -->
<div id="courier-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-4 border-b flex items-center justify-between bg-gray-50">
            <h3 class="text-lg font-bold">Kurye Yönetimi</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Vardiya Bilgisi -->
            <div id="shift-info" class="bg-gray-50 rounded-lg p-4">
                <!-- JS ile doldurulacak -->
            </div>
            
            <!-- Atanmış Kuryeler -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Atanmış Kuryeler
                </h4>
                <div id="assigned-couriers" class="space-y-2">
                    <!-- JS ile doldurulacak -->
                </div>
            </div>
            
            <!-- Tamamlanmış Kuryeler (Erken bitenler) -->
            <div id="completed-couriers-section" class="hidden">
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tamamlanan Kuryeler
                </h4>
                <div id="completed-couriers" class="space-y-2">
                    <!-- JS ile doldurulacak -->
                </div>
            </div>
            
            <!-- Uygun Kuryeler -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Kurye Ekle
                </h4>
                <!-- Arama Kutusu -->
                <div class="relative mb-2">
                    <input type="text" id="courier-search" placeholder="Kurye ara..." 
                           class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           oninput="filterCouriers(this.value)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div id="available-couriers" class="space-y-2 max-h-48 overflow-y-auto">
                    <!-- JS ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kurye Değişikliği Modal -->
<div id="change-courier-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="p-4 border-b flex items-center justify-between bg-orange-50">
            <h3 class="text-lg font-bold text-orange-800">Kurye Değişikliği</h3>
            <button onclick="closeChangeModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-4 space-y-4">
            <!-- Mevcut Kurye Bilgisi -->
            <div id="change-courier-info" class="bg-gray-50 rounded-lg p-4">
                <!-- JS ile doldurulacak -->
            </div>
            
            <!-- Adım 1: Bitiş Saati Seçimi -->
            <div id="end-time-step">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Saati</label>
                <input type="time" id="end-time-input" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <p class="text-xs text-gray-500 mt-1">Kuryenin vardiyayı bıraktığı saati seçin</p>
                
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sebep (Opsiyonel)</label>
                    <input type="text" id="end-reason-input" placeholder="Örn: Kaza, hastalık, acil durum..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                </div>
                
                <button onclick="endCourierEarly()" class="w-full mt-4 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Vardiyayı Bitir
                </button>
            </div>
            
            <!-- Adım 2: Yeni Kurye Seçimi (Başlangıçta gizli) -->
            <div id="new-courier-step" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-green-800">
                        <strong>Kalan süre:</strong> <span id="remaining-time-info"></span>
                    </p>
                </div>
                
                <label class="block text-sm font-medium text-gray-700 mb-2">Yeni Kurye Seç</label>
                <div class="relative mb-2">
                    <input type="text" id="new-courier-search" placeholder="Kurye ara..." 
                           class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                           oninput="filterNewCouriers(this.value)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div id="new-courier-list" class="space-y-2 max-h-48 overflow-y-auto">
                    <!-- JS ile doldurulacak -->
                </div>
                
                <button onclick="closeChangeModal(); loadShiftData(currentShiftId);" class="w-full mt-4 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Atamadan Kapat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentShiftId = null;
let currentRegionId = null;
let currentShiftData = null;
let allAvailableCouriers = [];
let changingCourierId = null;
let changingCourierData = null;
let remainingStartTime = null;

// Türkçe karakter desteği için
function turkishLowerCase(str) {
    return str.replace(/İ/g, 'i').replace(/I/g, 'ı').replace(/Ş/g, 'ş').replace(/Ğ/g, 'ğ').replace(/Ü/g, 'ü').replace(/Ö/g, 'ö').replace(/Ç/g, 'ç').toLowerCase();
}

function openCourierModal(shiftId) {
    currentShiftId = shiftId;
    document.getElementById('courier-modal').classList.remove('hidden');
    document.getElementById('courier-search').value = '';
    loadShiftData(shiftId);
}

function closeModal() {
    document.getElementById('courier-modal').classList.add('hidden');
    currentShiftId = null;
    currentRegionId = null;
    currentShiftData = null;
    allAvailableCouriers = [];
}

async function loadShiftData(shiftId) {
    try {
        // Vardiya bilgilerini yükle
        const shiftResponse = await fetch(`/panel/schedule/shifts/${shiftId}?t=${Date.now()}`, { cache: 'no-store' });
        const shiftData = await shiftResponse.json();
        
        // Region ID'yi ve vardiya verisini sakla
        currentRegionId = shiftData.region_id;
        currentShiftData = shiftData;
        
        // Vardiya bilgisi
        document.getElementById('shift-info').innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-lg">${shiftData.region?.name || 'Bölge Yok'}</p>
                    <p class="text-sm text-gray-500">${shiftData.shift_date} | ${shiftData.start_time} - ${shiftData.end_time}</p>
                    ${shiftData.title ? `<p class="text-sm text-gray-600 mt-1">${shiftData.title}</p>` : ''}
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold ${shiftData.assigned_count >= shiftData.required_couriers ? 'text-green-600' : 'text-orange-500'}">
                        ${shiftData.assigned_count}/${shiftData.required_couriers}
                    </p>
                    <p class="text-xs text-gray-500">kurye</p>
                </div>
            </div>
        `;
        
        // Atanmış kuryeler (aktif olanlar - erken bitmemişler)
        const assignedDiv = document.getElementById('assigned-couriers');
        const activeAssignments = shiftData.active_assignments?.filter(a => !a.has_ended_early) || [];
        
        if (activeAssignments.length > 0) {
            assignedDiv.innerHTML = activeAssignments.map(a => {
                // Geç başlama veya başlatmamış kontrolü
                let isLate = false;
                let notStarted = false;
                let statusBadge = '';
                
                const hasStarted = !!a.started_at;
                if (hasStarted) {
                    const scheduledStart = new Date(a.shift_date + 'T' + a.shift_start_time);
                    const actualStart = new Date(a.started_at);
                    const tolerance = 5 * 60 * 1000;
                    isLate = actualStart.getTime() > (scheduledStart.getTime() + tolerance);
                    if (isLate) {
                        statusBadge = '<span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-orange-100 text-orange-800 rounded">Geç Başlamış</span>';
                    } else {
                        statusBadge = '<span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">Vardiyayı Başlattı</span>';
                    }
                } else {
                    const scheduledStart = new Date(a.shift_date + 'T' + a.shift_start_time);
                    const now = new Date();
                    const tolerance = 5 * 60 * 1000;
                    notStarted = now.getTime() > (scheduledStart.getTime() + tolerance);
                    if (notStarted) {
                        statusBadge = '<span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 rounded">Vardiyaya Girmedi</span>';
                    } else {
                        statusBadge = '<span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 rounded">Atanmış</span>';
                    }
                }
                
                const bgColor = notStarted ? 'bg-red-50 border-red-200' : (isLate ? 'bg-orange-50 border-orange-200' : (hasStarted ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200'));
                const avatarColor = notStarted ? 'bg-red-600' : (isLate ? 'bg-orange-600' : (hasStarted ? 'bg-blue-600' : 'bg-green-600'));
                
                return `
                <div class="flex items-center justify-between p-3 ${bgColor} border rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 ${avatarColor} text-white rounded-full flex items-center justify-center font-bold">
                            ${a.courier.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                            <div class="flex items-center">
                                <p class="font-medium">${a.courier.name}</p>
                                ${statusBadge}
                            </div>
                            <p class="text-xs text-gray-500">${a.effective_start_time} - ${a.effective_end_time}</p>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="openChangeModal(${a.courier.id}, '${a.courier.name}', '${a.effective_start_time}', '${a.effective_end_time}')" 
                                class="px-2 py-1.5 text-xs bg-orange-100 text-orange-600 rounded hover:bg-orange-200" title="Kurye Değişikliği">
                            Değiştir
                        </button>
                        <button onclick="removeCourier(${shiftId}, ${a.courier.id})" 
                                class="px-2 py-1.5 text-xs bg-red-100 text-red-600 rounded hover:bg-red-200" title="Çıkar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            }).join('');
        } else {
            assignedDiv.innerHTML = `
                <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p>Henüz kurye atanmamış</p>
                </div>
            `;
        }
        
        // Tamamlanmış kuryeler (erken bitenler)
        const completedSection = document.getElementById('completed-couriers-section');
        const completedDiv = document.getElementById('completed-couriers');
        const completedAssignments = shiftData.all_assignments?.filter(a => a.has_ended_early) || [];
        
        if (completedAssignments.length > 0) {
            completedSection.classList.remove('hidden');
            completedDiv.innerHTML = completedAssignments.map(a => `
                <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-400 text-white rounded-full flex items-center justify-center font-bold">
                            ${a.courier.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                            <p class="font-medium text-gray-600">${a.courier.name}</p>
                            <p class="text-xs text-gray-500">${a.effective_start_time} - ${a.effective_end_time} (${a.worked_duration})</p>
                            ${a.end_reason ? `<p class="text-xs text-orange-500">${a.end_reason}</p>` : ''}
                        </div>
                    </div>
                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Tamamlandı</span>
                </div>
            `).join('');
        } else {
            completedSection.classList.add('hidden');
        }
        
        // Uygun kuryeleri yükle (bölgeye göre filtrelenmiş)
        const currentDate = '{{ $selectedDate }}';
        const couriersResponse = await fetch(`/panel/schedule/couriers?shift_id=${shiftId}&region_id=${currentRegionId}&date=${currentDate}&t=${Date.now()}`, { cache: 'no-store' });
        const couriersData = await couriersResponse.json();
        
        // Tüm kuryeleri sakla (arama için)
        allAvailableCouriers = couriersData.couriers || [];
        
        renderAvailableCouriers(allAvailableCouriers);
    } catch (error) {
        console.error('Error:', error);
    }
}

function renderAvailableCouriers(couriers) {
    const availableDiv = document.getElementById('available-couriers');
    
    if (couriers && couriers.length > 0) {
        availableDiv.innerHTML = couriers.map(c => `
            <div class="courier-item flex items-center justify-between p-3 border rounded-lg hover:bg-blue-50 transition-colors" data-name="${c.name}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center font-bold">
                        ${c.name.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-medium">${c.name}</p>
                        <p class="text-xs text-gray-500">${c.phone || '-'}</p>
                    </div>
                </div>
                <button onclick="assignCourier(${currentShiftId}, ${c.id})" 
                        class="px-3 py-1.5 text-sm bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Ekle
                </button>
            </div>
        `).join('');
    } else {
        availableDiv.innerHTML = `
            <div class="text-center py-4 text-gray-400 bg-gray-50 rounded-lg">
                <p>Bu bölgede uygun kurye bulunamadı</p>
            </div>
        `;
    }
}

function filterCouriers(searchText) {
    const searchLower = turkishLowerCase(searchText.trim());
    
    if (!searchLower) {
        renderAvailableCouriers(allAvailableCouriers);
        return;
    }
    
    const filtered = allAvailableCouriers.filter(c => 
        turkishLowerCase(c.name).includes(searchLower)
    );
    
    renderAvailableCouriers(filtered);
}

async function assignCourier(shiftId, courierId) {
    try {
        const response = await fetch(`/panel/schedule/shifts/${shiftId}/assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ courier_id: courierId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadShiftData(shiftId);
            updatePageStats();
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    }
}

async function removeCourier(shiftId, courierId) {
    if (!confirm('Bu kuryeyi vardiyadan çıkarmak istediğinize emin misiniz?')) return;
    
    try {
        const response = await fetch(`/panel/schedule/shifts/${shiftId}/unassign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ courier_id: Number(courierId) }),
            credentials: 'same-origin'
        });
        
        const contentType = response.headers.get('content-type');
        const isJson = contentType && contentType.includes('application/json');
        const data = isJson ? await response.json() : null;
        
        if (response.ok && data && data.success) {
            loadShiftData(shiftId);
            updatePageStats();
        } else if (response.status === 419) {
            alert('Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin.');
        } else if (data && data.message) {
            alert(data.message);
        } else if (data && data.errors && data.errors.courier_id) {
            alert(data.errors.courier_id[0] || 'Geçersiz istek.');
        } else {
            alert(data?.message || 'Kurye kaldırılırken bir hata oluştu. Sayfayı yenileyip tekrar deneyin.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kurye kaldırılırken bir hata oluştu. Sayfayı yenileyip tekrar deneyin.');
    }
}

// ==================== KURYE DEĞİŞİKLİĞİ ====================

function openChangeModal(courierId, courierName, startTime, endTime) {
    changingCourierId = courierId;
    changingCourierData = { name: courierName, startTime, endTime };
    
    // Modal içeriğini doldur
    document.getElementById('change-courier-info').innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                ${courierName.substring(0, 2).toUpperCase()}
            </div>
            <div>
                <p class="font-bold">${courierName}</p>
                <p class="text-sm text-gray-500">Çalışma Saati: ${startTime} - ${endTime}</p>
            </div>
        </div>
    `;
    
    // Bitiş saati inputunu ayarla
    document.getElementById('end-time-input').value = '';
    document.getElementById('end-time-input').min = startTime;
    document.getElementById('end-time-input').max = endTime;
    document.getElementById('end-reason-input').value = '';
    
    // Adım 1'i göster, Adım 2'yi gizle
    document.getElementById('end-time-step').classList.remove('hidden');
    document.getElementById('new-courier-step').classList.add('hidden');
    
    document.getElementById('change-courier-modal').classList.remove('hidden');
}

function closeChangeModal() {
    document.getElementById('change-courier-modal').classList.add('hidden');
    changingCourierId = null;
    changingCourierData = null;
    remainingStartTime = null;
}

async function endCourierEarly() {
    const endTime = document.getElementById('end-time-input').value;
    const reason = document.getElementById('end-reason-input').value;
    
    if (!endTime) {
        alert('Lütfen bitiş saati seçin');
        return;
    }
    
    try {
        const response = await fetch(`/panel/schedule/shifts/${currentShiftId}/end-courier`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                courier_id: changingCourierId,
                end_time: endTime,
                reason: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            remainingStartTime = data.remaining_start_time;
            
            // Bilgi kutusunu güncelle
            document.getElementById('change-courier-info').innerHTML = `
                <div class="text-center">
                    <p class="text-green-600 font-bold mb-2">✓ ${changingCourierData.name} vardiyayı tamamladı</p>
                    <p class="text-sm text-gray-600">Çalışma: ${data.assignment.start_time} - ${data.assignment.end_time}</p>
                    <p class="text-sm font-medium">${data.assignment.worked_duration}</p>
                </div>
            `;
            
            // Kalan süre bilgisi
            document.getElementById('remaining-time-info').textContent = 
                `${data.remaining_start_time} - ${data.remaining_end_time}`;
            
            // Adım 1'i gizle, Adım 2'yi göster
            document.getElementById('end-time-step').classList.add('hidden');
            document.getElementById('new-courier-step').classList.remove('hidden');
            
            // Yeni kurye listesini yükle
            loadNewCourierList();
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    }
}

async function loadNewCourierList() {
    try {
        const currentDate = '{{ $selectedDate }}';
        const response = await fetch(`/panel/schedule/couriers?shift_id=${currentShiftId}&region_id=${currentRegionId}&date=${currentDate}`);
        const data = await response.json();
        
        renderNewCourierList(data.couriers || []);
    } catch (error) {
        console.error('Error:', error);
    }
}

function renderNewCourierList(couriers) {
    const listDiv = document.getElementById('new-courier-list');
    
    if (couriers && couriers.length > 0) {
        listDiv.innerHTML = couriers.map(c => `
            <div class="courier-item flex items-center justify-between p-3 border rounded-lg hover:bg-green-50 transition-colors" data-name="${c.name}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center font-bold">
                        ${c.name.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-medium">${c.name}</p>
                        <p class="text-xs text-gray-500">${c.phone || '-'}</p>
                    </div>
                </div>
                <button onclick="assignNewCourier(${c.id})" 
                        class="px-3 py-1.5 text-sm bg-green-100 text-green-600 rounded-lg hover:bg-green-200">
                    Ata
                </button>
            </div>
        `).join('');
    } else {
        listDiv.innerHTML = `<p class="text-center py-4 text-gray-400">Uygun kurye bulunamadı</p>`;
    }
}

function filterNewCouriers(searchText) {
    const searchLower = turkishLowerCase(searchText.trim());
    const items = document.querySelectorAll('#new-courier-list .courier-item');
    
    items.forEach(item => {
        const name = turkishLowerCase(item.dataset.name || '');
        item.style.display = (!searchLower || name.includes(searchLower)) ? '' : 'none';
    });
}

async function assignNewCourier(courierId) {
    try {
        const response = await fetch(`/panel/schedule/shifts/${currentShiftId}/assign-with-time`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                courier_id: courierId,
                start_time: remainingStartTime
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeChangeModal();
            loadShiftData(currentShiftId);
            updatePageStats();
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    }
}

function updatePageStats() {
    location.reload();
}

// Modal dışına tıklayınca kapat
document.getElementById('courier-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('change-courier-modal').addEventListener('click', function(e) {
    if (e.target === this) closeChangeModal();
});

// ESC tuşu ile kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeChangeModal();
        closeModal();
    }
});
</script>
@endsection
