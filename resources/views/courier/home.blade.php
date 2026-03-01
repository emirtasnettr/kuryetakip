@extends('layouts.courier')

@section('title', 'Ana Sayfa')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Hoş geldin</p>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
            </div>
            <div class="text-right">
                <p class="text-gray-500 text-sm">{{ now()->translatedFormat('d F Y') }}</p>
                <p class="text-gray-600 font-medium">{{ now()->locale('tr')->dayName }}</p>
            </div>
        </div>
    </div>
    
    <!-- Active Shift Status -->
    @if($activeShift)
        <div class="bg-black text-white rounded-xl shadow-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-green-400 rounded-full animate-pulse mr-2"></span>
                    <span class="font-semibold">Aktif Vardiya</span>
                </div>
                <span class="text-gray-300 text-sm">{{ $activeShift->started_at->format('H:i') }}'den beri</span>
            </div>
            
            @if($activeAssignment && $activeAssignment->scheduledShift)
                <div class="bg-gray-800 rounded-lg p-3 mb-3">
                    <p class="text-sm text-gray-400">{{ $activeAssignment->scheduledShift->region->name ?? 'Bölge' }}</p>
                    <p class="text-white font-medium">
                        {{ \Carbon\Carbon::parse($activeAssignment->scheduledShift->start_time)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($activeAssignment->scheduledShift->end_time)->format('H:i') }}
                    </p>
                </div>
            @endif
            
            <div class="bg-gray-800 rounded-lg p-3 mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-300">Süre:</span>
                    <span class="font-bold text-lg" id="duration">{{ $activeShift->formatted_duration }}</span>
                </div>
            </div>
            
            <a href="{{ route('courier.shift.end') }}" 
               class="block w-full bg-white text-black text-center py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Vardiyayı Bitir
            </a>
        </div>
    @else
        <!-- Bugünkü Atanmış Vardiyalar -->
        @if($todayAssignments->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Bugünkü Vardiyalarınız</h3>
                </div>
                <div class="divide-y">
                    @foreach($todayAssignments as $assignment)
                        @php
                            $startTime = \Carbon\Carbon::parse($assignment->scheduledShift->start_time)->format('H:i');
                            $canStartAt = \Carbon\Carbon::parse($assignment->scheduledShift->start_time)->subMinutes(10)->format('H:i');
                        @endphp
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-bold text-gray-900">
                                        {{ $startTime }} - 
                                        {{ \Carbon\Carbon::parse($assignment->scheduledShift->end_time)->format('H:i') }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $assignment->scheduledShift->region->name ?? 'Bölge' }}</p>
                                </div>
                                <button class="shift-start-btn px-4 py-2 bg-black text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors"
                                   data-start-time="{{ $startTime }}"
                                   data-can-start-at="{{ $canStartAt }}"
                                   data-url="{{ route('courier.shift.start', ['assignment_id' => $assignment->id]) }}">
                                    Başlat
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            @if($upcomingAssignments->isNotEmpty())
                @php
                    $nextAssignment = $upcomingAssignments->first();
                @endphp
                <div class="bg-yellow-50 rounded-xl shadow-sm overflow-hidden border border-yellow-200">
                    <div class="p-4 border-b border-yellow-200 bg-yellow-100">
                        <h3 class="font-semibold text-yellow-900">Yaklaşan Vardiya</h3>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-yellow-700">{{ $nextAssignment->scheduledShift->shift_date->translatedFormat('d M, l') }}</p>
                                <p class="font-medium text-yellow-900">
                                    {{ \Carbon\Carbon::parse($nextAssignment->scheduledShift->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($nextAssignment->scheduledShift->end_time)->format('H:i') }}
                                </p>
                                <p class="text-sm text-yellow-600">{{ $nextAssignment->scheduledShift->region->name ?? 'Bölge' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs bg-yellow-200 text-yellow-800 rounded font-medium">Planlandı</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-800 font-semibold mb-2">Bugün Vardiya Yok</h3>
                    <p class="text-gray-500 text-sm">Bugün için atanmış vardiyanız bulunmuyor</p>
                </div>
            @endif
        @endif
        
        <!-- Gelecek Vardiyalar (İlk vardiya hariç) -->
        @if($upcomingAssignments->count() > 1)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Gelecek Vardiyalar</h3>
                </div>
                <div class="divide-y">
                    @foreach($upcomingAssignments->skip(1) as $assignment)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $assignment->scheduledShift->shift_date->translatedFormat('d M, l') }}</p>
                                    <p class="font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($assignment->scheduledShift->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($assignment->scheduledShift->end_time)->format('H:i') }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $assignment->scheduledShift->region->name ?? 'Bölge' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Planlandı</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Her zaman görünsün: Hakediş, Fotoğraf Talepleri, Masraf Talebi, Vardiya -->
    <!-- Hakedişim -->
    <a href="{{ route('courier.settlement') }}" class="block bg-white rounded-xl shadow-sm p-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-700 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-800">Hakedişim</div>
                <div class="text-xs text-gray-500">Tarih aralığına göre kazanç özeti</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- Fotoğraf Talepleri -->
    <a href="{{ route('courier.photo-retry') }}" class="block bg-white rounded-xl shadow-sm p-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 {{ isset($shiftsNeedingPhotoRetry) && $shiftsNeedingPhotoRetry->isNotEmpty() ? 'bg-amber-500' : 'bg-gray-600' }} rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-800">Fotoğraf Talepleri</div>
                <div class="text-xs text-gray-500">
                    @if(isset($shiftsNeedingPhotoRetry) && $shiftsNeedingPhotoRetry->isNotEmpty())
                        {{ $shiftsNeedingPhotoRetry->count() }} talep var — tekrar vardiya başlangıç fotoğrafı yükleyin
                    @else
                        Tekrar fotoğraf istenen vardiya yok
                    @endif
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- Masraf Talebi -->
    <a href="{{ route('courier.expenses.index') }}" class="block bg-white rounded-xl shadow-sm p-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5 5l6-6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-800">Masraf Talebi</div>
                <div class="text-xs text-gray-500">Masraf talebi oluştur veya geçmiş talepleri gör</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>

    <!-- Vardiya -->
    <a href="{{ route('courier.assignments') }}" class="block bg-white rounded-xl shadow-sm p-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-800">Vardiya</div>
                <div class="text-xs text-gray-500">Atanmış tüm vardiyalarım</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </a>
    
    <!-- Today's Stats -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-gray-800 font-semibold mb-4">Bugünün Özeti</h3>
        
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-black">{{ $todayStats['shift_count'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Vardiya</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-black">{{ $todayStats['total_packages'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Paket</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-black">
                    {{ floor($todayStats['total_minutes'] / 60) }}:{{ str_pad($todayStats['total_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Saat</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('courier.shifts') }}" class="bg-white rounded-xl shadow-sm p-4 flex items-center space-x-3 hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <div class="font-medium text-gray-800">Geçmiş</div>
                <div class="text-xs text-gray-500">Vardiyalarım</div>
            </div>
        </a>
        
        <a href="{{ route('courier.profile') }}" class="bg-white rounded-xl shadow-sm p-4 flex items-center space-x-3 hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <div class="font-medium text-gray-800">Profil</div>
                <div class="text-xs text-gray-500">Bilgilerim</div>
            </div>
        </a>
    </div>
    
</div>

<!-- Erken Başlatma Uyarı Modal -->
<div id="early-start-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Henüz Başlatamazsınız</h3>
        <p class="text-gray-600 mb-2">
            <span class="font-semibold" id="modal-shift-time"></span> vardiyası için
        </p>
        <p class="text-gray-500 text-sm mb-6">
            En erken <span class="font-bold text-black" id="modal-can-start-time"></span>'de başlatabilirsiniz.
        </p>
        <button onclick="closeEarlyStartModal()" class="w-full bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
            Tamam
        </button>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Başlat butonlarına event listener ekle
        document.querySelectorAll('.shift-start-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const startTime = this.dataset.startTime;
                const canStartAt = this.dataset.canStartAt;
                const url = this.dataset.url;
                
                const now = new Date();
                const currentMinutes = now.getHours() * 60 + now.getMinutes();
                
                const [canStartHours, canStartMins] = canStartAt.split(':').map(Number);
                const canStartMinutes = canStartHours * 60 + canStartMins;
                
                if (currentMinutes < canStartMinutes) {
                    // Henüz başlatılamaz, modal göster
                    document.getElementById('modal-shift-time').textContent = startTime;
                    document.getElementById('modal-can-start-time').textContent = canStartAt;
                    document.getElementById('early-start-modal').classList.remove('hidden');
                    document.getElementById('early-start-modal').classList.add('flex');
                } else {
                    // Başlatılabilir, sayfaya yönlendir
                    window.location.href = url;
                }
            });
        });
        
        // Modal dışına tıklayınca kapat
        const modal = document.getElementById('early-start-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeEarlyStartModal();
            });
        }
    });
    
    function closeEarlyStartModal() {
        document.getElementById('early-start-modal').classList.add('hidden');
        document.getElementById('early-start-modal').classList.remove('flex');
    }
</script>
@endpush

@if($activeShift)
@push('scripts')
<script>
    let shiftStartTime = new Date('{{ $activeShift->started_at->toIso8601String() }}');
    
    function updateDuration() {
        let now = new Date();
        let diff = Math.floor((now - shiftStartTime) / 1000);
        
        let hours = Math.floor(diff / 3600);
        let minutes = Math.floor((diff % 3600) / 60);
        
        let text = '';
        if (hours > 0) {
            text = hours + ' saat ' + minutes + ' dakika';
        } else {
            text = minutes + ' dakika';
        }
        
        document.getElementById('duration').textContent = text;
    }
    
    setInterval(updateDuration, 60000);
</script>
@endpush
@endif
@endsection
