@extends('layouts.courier')

@section('title', 'Profilim')

@section('content')
<div class="p-4 space-y-4">
    
    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
        <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl font-bold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
        </div>
        <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
        <p class="text-gray-500">{{ $user->email }}</p>
        @if($user->employee_code)
            <p class="text-sm text-black font-medium mt-1">{{ $user->employee_code }}</p>
        @endif
    </div>
    
    <!-- Monthly Stats -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="font-semibold text-gray-800 mb-4">Bu Ay</h3>
        
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-black">{{ $monthlyStats['shift_count'] }}</div>
                <div class="text-xs text-gray-500">Vardiya</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-black">{{ $monthlyStats['total_packages'] }}</div>
                <div class="text-xs text-gray-500">Paket</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-black">{{ $monthlyStats['total_hours'] }}</div>
                <div class="text-xs text-gray-500">Saat</div>
            </div>
        </div>
    </div>
    
    <!-- Info Details -->
    <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
        @if($user->phone)
            <div class="p-4 flex items-center justify-between">
                <span class="text-gray-500">Telefon</span>
                <span class="font-medium text-gray-800">{{ $user->phone }}</span>
            </div>
        @endif
        
        @if($user->vehicle_type)
            <div class="p-4 flex items-center justify-between">
                <span class="text-gray-500">Araç Tipi</span>
                <span class="font-medium text-gray-800">{{ $user->vehicle_type }}</span>
            </div>
        @endif
        
        @if($user->vehicle_plate)
            <div class="p-4 flex items-center justify-between">
                <span class="text-gray-500">Plaka</span>
                <span class="font-medium text-gray-800">{{ $user->vehicle_plate }}</span>
            </div>
        @endif
        
        @if($user->partner)
            <div class="p-4 flex items-center justify-between">
                <span class="text-gray-500">İş Ortağı</span>
                <span class="font-medium text-gray-800">{{ $user->partner->name }}</span>
            </div>
        @endif
    </div>
    
    <!-- Working Districts -->
    @if($user->courierDistricts->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Çalışma Bölgeleri</h3>
            
            <div class="flex flex-wrap gap-2">
                @foreach($user->courierDistricts as $district)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm 
                        {{ $district->pivot->is_primary ? 'bg-black text-white' : 'bg-gray-100 text-gray-700' }}">
                        {{ $district->name }}
                        @if($district->pivot->is_primary)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Logout Button -->
    <form action="{{ route('courier.logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full bg-gray-100 text-gray-700 py-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
            Çıkış Yap
        </button>
    </form>
    
</div>
@endsection
