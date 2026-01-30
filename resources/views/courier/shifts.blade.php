@extends('layouts.courier')

@section('title', 'Vardiya Geçmişi')

@section('content')
<div class="p-4">
    
    @if($shifts->isEmpty())
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="text-gray-500 font-medium">Henüz vardiya kaydınız yok</h3>
            <p class="text-gray-400 text-sm mt-1">İlk vardiyanızı başlatın</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($shifts as $shift)
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2 
                                {{ $shift->status === 'active' ? 'bg-green-500 animate-pulse' : '' }}
                                {{ $shift->status === 'completed' ? 'bg-black' : '' }}
                                {{ $shift->status === 'cancelled' ? 'bg-red-500' : '' }}
                            "></span>
                            <span class="text-sm font-medium 
                                {{ $shift->status === 'active' ? 'text-green-600' : '' }}
                                {{ $shift->status === 'completed' ? 'text-black' : '' }}
                                {{ $shift->status === 'cancelled' ? 'text-red-600' : '' }}
                            ">
                                {{ $shift->status === 'active' ? 'Aktif' : '' }}
                                {{ $shift->status === 'completed' ? 'Tamamlandı' : '' }}
                                {{ $shift->status === 'cancelled' ? 'İptal' : '' }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">{{ $shift->started_at->translatedFormat('d F Y') }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm mb-2">
                        <div class="flex items-center text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $shift->started_at->format('H:i') }}
                            @if($shift->ended_at)
                                - {{ $shift->ended_at->format('H:i') }}
                            @endif
                        </div>
                        @if($shift->status === 'completed' || $shift->total_minutes)
                            <span class="text-gray-500">{{ $shift->formatted_duration }}</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-between">
                        @if($shift->district)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                {{ $shift->district->name }}
                            </span>
                        @else
                            <span></span>
                        @endif
                        
                        @if($shift->status === 'completed' && $shift->package_count !== null)
                            <span class="text-sm font-semibold text-black">
                                {{ $shift->package_count }} paket
                            </span>
                        @endif
                    </div>
                    
                    @if($shift->start_location_url || $shift->end_location_url)
                        <div class="mt-3 pt-3 border-t border-gray-100 flex space-x-3">
                            @if($shift->start_location_url)
                                <a href="{{ $shift->start_location_url }}" target="_blank" class="text-xs text-gray-600 hover:text-black flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    Başlangıç
                                </a>
                            @endif
                            @if($shift->end_location_url)
                                <a href="{{ $shift->end_location_url }}" target="_blank" class="text-xs text-gray-600 hover:text-black flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    Bitiş
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $shifts->links() }}
        </div>
    @endif
    
</div>
@endsection
