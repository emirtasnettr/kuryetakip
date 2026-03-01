@extends('layouts.panel')

@section('title', 'Günlük Vardiya Görünümü - ' . $date->format('d.m.Y'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('panel.schedule.day', $date->copy()->subDay()->format('Y-m-d')) }}" 
               class="p-2 text-gray-600 hover:text-black rounded-lg hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold">{{ $date->translatedFormat('d F Y, l') }}</h2>
                @if($date->isToday())
                    <span class="text-sm text-green-600 font-medium">Bugün</span>
                @endif
            </div>
            <a href="{{ route('panel.schedule.day', $date->copy()->addDay()->format('Y-m-d')) }}" 
               class="p-2 text-gray-600 hover:text-black rounded-lg hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('panel.schedule.calendar') }}" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Takvime Dön
            </a>
        </div>
    </div>

    <!-- Bölge Filtresi -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Bölge:</label>
            <select name="district_id" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 text-sm focus:ring-black focus:border-black">
                <option value="">Tüm Bölgeler</option>
                @foreach($districts as $district)
                    <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                        {{ $district->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Vardiya Grid -->
    @if($shifts->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500">Bu gün için planlanmış vardiya yok.</p>
            <a href="{{ route('panel.schedule.calendar') }}" class="inline-block mt-4 px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800">
                Vardiya Oluştur
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($shifts as $shift)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Üst Bar (Renk) -->
                    <div class="h-2" style="background-color: {{ $shift->color }}"></div>
                    
                    <div class="p-6">
                        <!-- Başlık ve Bölge -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-lg">{{ $shift->display_title }}</h3>
                                <p class="text-sm text-gray-500">{{ $shift->district->name }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $shift->isFullyStaffed() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $shift->assigned_count }}/{{ $shift->required_couriers }}
                            </span>
                        </div>
                        
                        <!-- Saat -->
                        <div class="flex items-center gap-2 text-gray-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">
                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                            </span>
                            <span class="text-sm text-gray-400">({{ $shift->formatted_duration }})</span>
                        </div>
                        
                        <!-- Atanmış Kuryeler -->
                        <div class="border-t pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Atanmış Kuryeler</p>
                            
                            @if($shift->assignments->isEmpty())
                                <p class="text-sm text-gray-400">Henüz kurye atanmamış</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($shift->assignments as $assignment)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-xs font-medium">
                                                    {{ substr($assignment->courier->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $assignment->courier->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $assignment->courier->phone }}</p>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $assignment->status_color }}-100 text-{{ $assignment->status_color }}-800">
                                                {{ $assignment->status_label }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <!-- Notlar -->
                        @if($shift->notes)
                            <div class="border-t mt-4 pt-4">
                                <p class="text-sm text-gray-500">{{ $shift->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Özet -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold mb-4">Günün Özeti</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Toplam Vardiya</p>
                <p class="text-2xl font-bold">{{ $shifts->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Gereken Kurye</p>
                <p class="text-2xl font-bold">{{ $shifts->sum('required_couriers') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Atanan Kurye</p>
                <p class="text-2xl font-bold text-green-600">{{ $shifts->sum('assigned_count') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Eksik</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $shifts->sum('remaining_capacity') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
