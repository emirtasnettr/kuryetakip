@extends('layouts.panel')

@section('title', 'Kuryeler')

@section('content')

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div></div>
    @can('create', App\Models\User::class)
        <a href="{{ route('panel.couriers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Yeni Kurye
        </a>
    @endcan
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('panel.couriers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="İsim, e-posta, telefon..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Tümü</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bölge</label>
            <select name="district_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Tümü</option>
                @foreach($districts as $district)
                    <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                        {{ $district->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                Filtrele
            </button>
        </div>
    </form>
</div>

<!-- Couriers Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($couriers as $courier)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 {{ $courier->is_active ? 'bg-indigo-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                            <span class="{{ $courier->is_active ? 'text-indigo-600' : 'text-gray-400' }} font-bold">
                                {{ strtoupper(substr($courier->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-semibold text-gray-800">{{ $courier->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $courier->employee_code ?? $courier->email }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $courier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $courier->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                </div>
                
                @if($courier->courierDistricts->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach($courier->courierDistricts->take(3) as $district)
                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                                {{ $district->name }}
                            </span>
                        @endforeach
                        @if($courier->courierDistricts->count() > 3)
                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                                +{{ $courier->courierDistricts->count() - 3 }}
                            </span>
                        @endif
                    </div>
                @endif
                
                @if($courier->phone)
                    <p class="text-sm text-gray-500 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $courier->phone }}
                    </p>
                @endif
            </div>
            
            <div class="bg-gray-50 px-6 py-3 border-t flex justify-between items-center">
                <a href="{{ route('panel.couriers.show', $courier) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Detay
                </a>
                @can('update', $courier)
                    <a href="{{ route('panel.couriers.edit', $courier) }}" class="text-gray-600 hover:text-gray-800 text-sm">
                        Düzenle
                    </a>
                @endcan
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-500 mb-2">Kurye Bulunamadı</h3>
            <p class="text-gray-400">Arama kriterlerinize uygun kurye yok.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $couriers->links() }}
</div>

@endsection
