@extends('layouts.panel')

@section('title', 'Vardiya Detayı')

@section('content')

<div class="mb-6">
    <a href="{{ route('panel.shifts.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Geri Dön
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-bold text-xl">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-gray-800">{{ $shift->user->name }}</h2>
                        <p class="text-gray-500">{{ $shift->user->employee_code ?? $shift->user->email }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    {{ $shift->status == 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $shift->status == 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $shift->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ $shift->status == 'active' ? 'Aktif' : '' }}
                    {{ $shift->status == 'completed' ? 'Tamamlandı' : '' }}
                    {{ $shift->status == 'cancelled' ? 'İptal' : '' }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $shift->formatted_duration }}</p>
                    <p class="text-sm text-gray-500">Süre</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-indigo-600">{{ $shift->package_count ?? '-' }}</p>
                    <p class="text-sm text-gray-500">Paket</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $shift->started_at->format('H:i') }}</p>
                    <p class="text-sm text-gray-500">Başlangıç</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $shift->ended_at?->format('H:i') ?? '-' }}</p>
                    <p class="text-sm text-gray-500">Bitiş</p>
                </div>
            </div>
        </div>
        
        <!-- Location Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Konum Bilgileri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Start Location -->
                <div class="p-4 bg-green-50 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2">Başlangıç Konumu</h4>
                    <p class="text-sm text-gray-600 mb-2">{{ $shift->started_at->format('d.m.Y H:i') }}</p>
                    @if($shift->start_latitude && $shift->start_longitude)
                        <p class="text-xs text-gray-500 mb-2">
                            {{ $shift->start_latitude }}, {{ $shift->start_longitude }}
                        </p>
                        <a href="{{ $shift->start_location_url }}" target="_blank" 
                           class="inline-flex items-center text-sm text-green-600 hover:text-green-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Haritada Göster
                        </a>
                    @else
                        <p class="text-sm text-gray-500">Konum bilgisi yok</p>
                    @endif
                </div>
                
                <!-- End Location -->
                <div class="p-4 bg-red-50 rounded-lg">
                    <h4 class="font-medium text-red-800 mb-2">Bitiş Konumu</h4>
                    @if($shift->ended_at)
                        <p class="text-sm text-gray-600 mb-2">{{ $shift->ended_at->format('d.m.Y H:i') }}</p>
                        @if($shift->end_latitude && $shift->end_longitude)
                            <p class="text-xs text-gray-500 mb-2">
                                {{ $shift->end_latitude }}, {{ $shift->end_longitude }}
                            </p>
                            <a href="{{ $shift->end_location_url }}" target="_blank" 
                               class="inline-flex items-center text-sm text-red-600 hover:text-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Haritada Göster
                            </a>
                        @else
                            <p class="text-sm text-gray-500">Konum bilgisi yok</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Henüz tamamlanmadı</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Photos -->
        @if($shift->photos->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Fotoğraflar</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($shift->photos as $photo)
                    <div class="relative group">
                        <img src="{{ $photo->url }}" alt="{{ $photo->type_display }}" 
                             class="w-full h-32 object-cover rounded-lg cursor-pointer"
                             onclick="window.open('{{ $photo->url }}', '_blank')">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs py-1 px-2 rounded-b-lg">
                            {{ $photo->type_display }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        
        <!-- Details Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detaylar</h3>
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm text-gray-500">Vardiya ID</dt>
                    <dd class="font-medium text-gray-800">#{{ $shift->id }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Tarih</dt>
                    <dd class="font-medium text-gray-800">{{ $shift->started_at->translatedFormat('d F Y') }}</dd>
                </div>
                @if($shift->district)
                <div>
                    <dt class="text-sm text-gray-500">Bölge</dt>
                    <dd class="font-medium text-gray-800">{{ $shift->district->name }}</dd>
                </div>
                @endif
                @if($shift->notes)
                <div>
                    <dt class="text-sm text-gray-500">Kurye Notu</dt>
                    <dd class="text-gray-800">{{ $shift->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>
        
        <!-- Admin Notes -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Yönetici Notu</h3>
            
            @if($shift->admin_notes)
                <p class="text-gray-600 mb-4">{{ $shift->admin_notes }}</p>
            @endif
            
            @can('addAdminNote', $shift)
            <form action="{{ route('panel.shifts.add-note', $shift) }}" method="POST">
                @csrf
                <textarea name="admin_notes" rows="3" placeholder="Not ekle..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 mb-3">{{ $shift->admin_notes }}</textarea>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    Notu Kaydet
                </button>
            </form>
            @endcan
        </div>
        
        <!-- Actions -->
        @if($shift->isActive())
        @can('cancel', $shift)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">İşlemler</h3>
            
            <form action="{{ route('panel.shifts.cancel', $shift) }}" method="POST" 
                  onsubmit="return confirm('Bu vardiyayı iptal etmek istediğinize emin misiniz?')">
                @csrf
                <textarea name="reason" rows="2" placeholder="İptal sebebi (opsiyonel)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 mb-3"></textarea>
                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Vardiyayı İptal Et
                </button>
            </form>
        </div>
        @endcan
        @endif
        
    </div>
    
</div>

@endsection
