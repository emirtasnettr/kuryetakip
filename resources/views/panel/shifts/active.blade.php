@extends('layouts.panel')

@section('title', 'Aktif Vardiyalar')

@section('content')

@if($shifts->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-500 mb-2">Aktif Vardiya Yok</h3>
        <p class="text-gray-400">Şu an çalışan kurye bulunmuyor.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($shifts as $shift)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 font-bold">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-800">{{ $shift->user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $shift->district?->name ?? 'Bölge yok' }}</p>
                            </div>
                        </div>
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Başlangıç:</span>
                            <span class="font-medium">{{ $shift->started_at->format('H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Süre:</span>
                            <span class="font-semibold text-green-600">{{ $shift->formatted_duration }}</span>
                        </div>
                        @if($shift->start_location_url)
                        <div class="pt-2">
                            <a href="{{ $shift->start_location_url }}" target="_blank" 
                               class="flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                Başlangıç konumu
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t">
                    <a href="{{ route('panel.shifts.show', $shift) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        Detayları Gör →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
