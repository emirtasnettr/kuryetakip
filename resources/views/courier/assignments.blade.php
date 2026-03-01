@extends('layouts.courier')

@section('title', 'Vardiyalarım')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('courier.home') }}" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800">Vardiyalarım</h1>
        </div>
    </div>
    
    <!-- Aktif ve Gelecek Vardiyalar -->
    @if($assignments->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-black">
                <h3 class="font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Planlanan Vardiyalar
                </h3>
            </div>
            <div class="divide-y">
                @foreach($assignments as $assignment)
                    @php
                        $shift = $assignment->scheduledShift;
                        $isToday = $shift->shift_date->isToday();
                        $isPast = $shift->shift_date->isPast() && !$isToday;
                        $statusClass = match($assignment->status) {
                            'started' => 'bg-green-100 text-green-700',
                            'completed' => 'bg-gray-100 text-gray-600',
                            'confirmed' => 'bg-blue-100 text-blue-700',
                            default => 'bg-yellow-100 text-yellow-700'
                        };
                        $statusText = match($assignment->status) {
                            'started' => 'Devam Ediyor',
                            'completed' => 'Tamamlandı',
                            'confirmed' => 'Onaylandı',
                            default => 'Atandı'
                        };
                    @endphp
                    <div class="p-4 {{ $isToday ? 'bg-green-50' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($isToday)
                                        <span class="px-2 py-0.5 text-xs bg-green-500 text-white rounded font-medium">Bugün</span>
                                    @endif
                                    <span class="text-sm text-gray-500">
                                        {{ $shift->shift_date->translatedFormat('d M Y, l') }}
                                    </span>
                                </div>
                                <p class="font-bold text-gray-900 text-lg">
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                </p>
                                <p class="text-gray-600">{{ $shift->region->name ?? 'Bölge' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                        
                        @if($isToday && $assignment->status === 'assigned')
                            <div class="mt-3">
                                <a href="{{ route('courier.shift.start', ['assignment_id' => $assignment->id]) }}" 
                                   class="block w-full text-center px-4 py-2 bg-black text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors">
                                    Vardiyayı Başlat
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-gray-800 font-semibold mb-2">Planlanan Vardiya Yok</h3>
            <p class="text-gray-500 text-sm">Henüz size atanmış bir vardiya bulunmuyor</p>
        </div>
    @endif
    
    <!-- Geçmiş Vardiyalar -->
    @if($pastAssignments->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Geçmiş Vardiyalar
                </h3>
            </div>
            <div class="divide-y">
                @foreach($pastAssignments as $assignment)
                    @php
                        $shift = $assignment->scheduledShift;
                        $statusClass = $assignment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600';
                        $statusText = $assignment->status === 'completed' ? 'Tamamlandı' : 'Geçti';
                    @endphp
                    <div class="p-4 opacity-75">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 mb-1">
                                    {{ $shift->shift_date->translatedFormat('d M Y, l') }}
                                </p>
                                <p class="font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                </p>
                                <p class="text-sm text-gray-500">{{ $shift->region->name ?? 'Bölge' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
</div>
@endsection
