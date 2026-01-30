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
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-gray-800 font-semibold mb-2">Aktif Vardiya Yok</h3>
            <p class="text-gray-500 text-sm mb-4">Çalışmaya başlamak için vardiya başlatın</p>
            
            <a href="{{ route('courier.shift.start') }}" 
               class="inline-flex items-center justify-center w-full bg-black text-white py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Vardiyaya Başla
            </a>
        </div>
    @endif
    
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

@if($activeShift)
@push('scripts')
<script>
    let startTime = new Date('{{ $activeShift->started_at->toIso8601String() }}');
    
    function updateDuration() {
        let now = new Date();
        let diff = Math.floor((now - startTime) / 1000);
        
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
