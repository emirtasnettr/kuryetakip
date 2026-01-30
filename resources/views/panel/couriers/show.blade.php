@extends('layouts.panel')

@section('title', 'Kurye Detayı')

@section('content')

<div class="mb-6">
    <a href="{{ route('panel.couriers.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Geri Dön
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-bold text-2xl">{{ strtoupper(substr($courier->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $courier->name }}</h2>
                        <p class="text-gray-500">{{ $courier->email }}</p>
                        @if($courier->employee_code)
                            <span class="text-sm text-indigo-600 font-medium">{{ $courier->employee_code }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $courier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $courier->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                    @can('update', $courier)
                        <a href="{{ route('panel.couriers.edit', $courier) }}" 
                           class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Düzenle
                        </a>
                    @endcan
                </div>
            </div>
            
            <!-- Monthly Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-indigo-600">{{ $monthlyStats['shift_count'] }}</p>
                    <p class="text-sm text-gray-500">Vardiya</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">{{ $monthlyStats['total_packages'] }}</p>
                    <p class="text-sm text-gray-500">Paket</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-orange-600">{{ $monthlyStats['total_hours'] }}</p>
                    <p class="text-sm text-gray-500">Saat</p>
                </div>
            </div>
            <p class="text-xs text-gray-400 text-center mt-2">Bu ay ({{ now()->translatedFormat('F Y') }})</p>
        </div>
        
        <!-- Recent Shifts -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Son Vardiyalar</h3>
                <a href="{{ route('panel.shifts.index') }}?courier_id={{ $courier->id }}" class="text-sm text-indigo-600 hover:underline">
                    Tümünü Gör
                </a>
            </div>
            
            <div class="divide-y divide-gray-200">
                @forelse($recentShifts as $shift)
                    <a href="{{ route('panel.shifts.show', $shift) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <span class="w-2 h-2 rounded-full mr-3
                                {{ $shift->status === 'active' ? 'bg-green-500' : '' }}
                                {{ $shift->status === 'completed' ? 'bg-blue-500' : '' }}
                                {{ $shift->status === 'cancelled' ? 'bg-red-500' : '' }}
                            "></span>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $shift->started_at->format('d.m.Y') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $shift->started_at->format('H:i') }} - {{ $shift->ended_at?->format('H:i') ?? 'Devam ediyor' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-indigo-600">{{ $shift->package_count ?? '-' }} paket</p>
                            <p class="text-xs text-gray-500">{{ $shift->formatted_duration }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        Henüz vardiya kaydı yok
                    </div>
                @endforelse
            </div>
        </div>
        
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        
        <!-- Details -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Bilgiler</h3>
            
            <dl class="space-y-4">
                @if($courier->phone)
                <div>
                    <dt class="text-sm text-gray-500">Telefon</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->phone }}</dd>
                </div>
                @endif
                
                @if($courier->vehicle_type)
                <div>
                    <dt class="text-sm text-gray-500">Araç Tipi</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->vehicle_type }}</dd>
                </div>
                @endif
                
                @if($courier->vehicle_plate)
                <div>
                    <dt class="text-sm text-gray-500">Plaka</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->vehicle_plate }}</dd>
                </div>
                @endif
                
                @if($courier->partner)
                <div>
                    <dt class="text-sm text-gray-500">İş Ortağı</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->partner->name }}</dd>
                </div>
                @endif
                
                <div>
                    <dt class="text-sm text-gray-500">Kayıt Tarihi</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->created_at->format('d.m.Y') }}</dd>
                </div>
                
                @if($courier->last_login_at)
                <div>
                    <dt class="text-sm text-gray-500">Son Giriş</dt>
                    <dd class="font-medium text-gray-800">{{ $courier->last_login_at->format('d.m.Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>
        
        <!-- Districts -->
        @if($courier->courierDistricts->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Çalışma Bölgeleri</h3>
            
            <div class="space-y-2">
                @foreach($courier->courierDistricts as $district)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm {{ $district->pivot->is_primary ? 'font-medium text-indigo-600' : 'text-gray-600' }}">
                            {{ $district->name }}
                        </span>
                        @if($district->pivot->is_primary)
                            <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded">Ana</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Actions -->
        @can('toggleActive', $courier)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">İşlemler</h3>
            
            <form action="{{ route('panel.couriers.toggle-active', $courier) }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full {{ $courier->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white py-2 rounded-lg transition-colors"
                        onclick="return confirm('{{ $courier->is_active ? 'Kuryeyi pasif yapmak istediğinize emin misiniz?' : 'Kuryeyi aktif yapmak istediğinize emin misiniz?' }}')">
                    {{ $courier->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                </button>
            </form>
        </div>
        @endcan
        
    </div>
    
</div>

@endsection
