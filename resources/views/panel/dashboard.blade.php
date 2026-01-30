@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Toplam Kurye</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $todayStats['total_couriers'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Aktif Vardiya</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $todayStats['active_shifts'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Bugün Tamamlanan</p>
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $todayStats['completed_shifts'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Bugün Paket</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ $todayStats['total_packages'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Active Shifts -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Aktif Vardiyalar</h2>
            <a href="{{ route('panel.shifts.active') }}" class="text-sm text-indigo-600 hover:underline">Tümünü Gör</a>
        </div>
        <div class="p-6">
            @if($activeShifts->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Şu an aktif vardiya bulunmuyor</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($activeShifts as $shift)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-semibold">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-800">{{ $shift->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $shift->district?->name ?? 'Bölge belirtilmedi' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-green-600">{{ $shift->formatted_duration }}</p>
                                <p class="text-xs text-gray-500">{{ $shift->started_at->format('H:i') }}'den beri</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Completed Today -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Bugün Tamamlanan</h2>
            <a href="{{ route('panel.shifts.index') }}?start_date={{ today()->format('Y-m-d') }}&status=completed" class="text-sm text-indigo-600 hover:underline">Tümünü Gör</a>
        </div>
        <div class="p-6">
            @if($completedShifts->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Bugün tamamlanan vardiya yok</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($completedShifts as $shift)
                        <a href="{{ route('panel.shifts.show', $shift) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-800">{{ $shift->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $shift->started_at->format('H:i') }} - {{ $shift->ended_at->format('H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-indigo-600">{{ $shift->package_count ?? 0 }} paket</p>
                                <p class="text-xs text-gray-500">{{ $shift->formatted_duration }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
</div>

@endsection
