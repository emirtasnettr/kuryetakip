@extends('layouts.panel')

@section('title', 'Kullanıcı Detay')

@section('content')
<div class="max-w-3xl mx-auto">
    
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('panel.users.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kullanıcılara Dön
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Kullanıcı Detayı</h2>
        </div>
        <a href="{{ route('panel.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Düzenle
        </a>
    </div>
    
    <!-- User Info -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                <p class="text-gray-500">{{ $user->email }}</p>
                <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($user->role->name === 'admin') bg-purple-100 text-purple-800
                        @elseif($user->role->name === 'courier') bg-blue-100 text-blue-800
                        @elseif($user->role->name === 'operation_manager') bg-green-100 text-green-800
                        @elseif($user->role->name === 'operation_specialist') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif
                    ">
                        {{ $user->role->display_name }}
                    </span>
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                            Pasif
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Telefon</p>
                <p class="font-medium text-gray-800">{{ $user->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Sicil No</p>
                <p class="font-medium text-gray-800">{{ $user->employee_code ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Kayıt Tarihi</p>
                <p class="font-medium text-gray-800">{{ $user->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Son Giriş</p>
                <p class="font-medium text-gray-800">{{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : '-' }}</p>
            </div>
        </div>
    </div>
    
    @if($user->isCourier())
        <!-- Kurye Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kurye Bilgileri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Araç Tipi</p>
                    <p class="font-medium text-gray-800">{{ $user->vehicle_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Plaka</p>
                    <p class="font-medium text-gray-800">{{ $user->vehicle_plate ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">İş Ortağı</p>
                    <p class="font-medium text-gray-800">{{ $user->partner?->name ?? '-' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Bölgeler -->
        @if($user->courierDistricts->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Çalışma Bölgeleri</h3>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($user->courierDistricts as $district)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm 
                            {{ $district->pivot->is_primary ? 'bg-black text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ $district->name }}
                            @if($district->pivot->is_primary)
                                <span class="ml-1 text-xs">(Ana)</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
    
    @if($user->role->name === 'operation_specialist' || $user->role->name === 'operation_manager')
        <!-- Yetkili Bölgeler -->
        @if($user->authorizedDistricts->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Yetkili Bölgeler</h3>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($user->authorizedDistricts as $district)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700">
                            {{ $district->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
    
</div>
@endsection
