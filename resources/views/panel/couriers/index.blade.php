@extends('layouts.panel')

@section('title', 'Kuryeler')

@section('content')

@php
    $isActiveTab = request('status') !== 'inactive';
@endphp

<!-- Tabs -->
<div class="border-b border-gray-200 mb-6">
    <nav class="flex gap-1" aria-label="Aktif / Pasif">
        <a href="{{ route('panel.couriers.index', array_merge(request()->query(), ['status' => 'active'])) }}" 
           class="px-4 py-3 text-sm font-medium rounded-t-lg border-b-2 transition-colors {{ $isActiveTab ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Aktif Kuryeler
        </a>
        <a href="{{ route('panel.couriers.index', array_merge(request()->query(), ['status' => 'inactive'])) }}" 
           class="px-4 py-3 text-sm font-medium rounded-t-lg border-b-2 transition-colors {{ !$isActiveTab ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Pasif Kuryeler
        </a>
    </nav>
</div>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $isActiveTab ? 'Aktif Kuryeler' : 'Pasif Kuryeler' }}</h1>
        <p class="text-gray-500 mt-1">Kurye bilgileri listesi. Filtreleyip Excel olarak indirebilirsiniz.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.couriers.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Excel İndir
        </a>
        @can('create', App\Models\User::class)
            <a href="{{ route('panel.couriers.create') }}" class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Kurye
            </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('panel.couriers.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="status" value="{{ request('status', 'active') }}">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="İsim, e-posta, telefon..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">İlçe</label>
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

<!-- Couriers Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($couriers->isEmpty())
        <div class="p-12 text-center text-gray-500">
            <p class="font-medium">Kurye bulunamadı</p>
            <p class="text-sm mt-1">Arama kriterlerinize uygun kurye yok.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ad Soyad</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-posta</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">T.C. Kimlik No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Araç / Plaka</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bölge</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş Ortağı</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Son Giriş</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlem</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($couriers as $courier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('panel.couriers.show', $courier) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                    {{ $courier->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $courier->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $courier->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $courier->employee_code ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($courier->vehicle_type || $courier->vehicle_plate)
                                    {{ $courier->vehicle_type ?? '—' }} {{ $courier->vehicle_plate ? ' · ' . $courier->vehicle_plate : '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($courier->courierRegions->isNotEmpty())
                                    {{ $courier->courierRegions->pluck('name')->take(2)->join(', ') }}{{ $courier->courierRegions->count() > 2 ? ' +' . ($courier->courierRegions->count() - 2) : '' }}
                                @elseif($courier->courierDistricts->isNotEmpty())
                                    {{ $courier->courierDistricts->pluck('name')->take(2)->join(', ') }}{{ $courier->courierDistricts->count() > 2 ? ' +' . ($courier->courierDistricts->count() - 2) : '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $courier->partner?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $courier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $courier->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $courier->last_login_at ? $courier->last_login_at->format('d.m.Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('panel.couriers.show', $courier) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-2">Detay</a>
                                @can('update', $courier)
                                    <a href="{{ route('panel.couriers.edit', $courier) }}" class="text-gray-600 hover:text-gray-800 text-sm">Düzenle</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $couriers->links() }}
        </div>
    @endif
</div>

@endsection
