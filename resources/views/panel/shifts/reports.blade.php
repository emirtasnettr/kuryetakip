@extends('layouts.panel')

@section('title', 'Raporlar')

@section('content')

<!-- Date Filter -->
<div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-6">
    <form method="GET" action="{{ route('panel.shifts.reports') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
            <input type="date" name="start_date" value="{{ $startDate }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
            <input type="date" name="end_date" value="{{ $endDate }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                Filtrele
            </button>
        </div>
    </form>
</div>

<!-- Overall Stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Toplam Vardiya</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-800 mt-1">{{ $overallStats['total_shifts'] }}</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Toplam Paket</p>
                <p class="text-2xl md:text-3xl font-bold text-indigo-600 mt-1">{{ number_format($overallStats['total_packages']) }}</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Toplam Çalışma Saati</p>
                <p class="text-2xl md:text-3xl font-bold text-green-600 mt-1">{{ number_format($overallStats['total_hours'], 1) }}</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Courier Report Header -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Kurye Bazlı Rapor</h2>
        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}</p>
    </div>
    
    <!-- Desktop: Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurye</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vardiya Sayısı</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Çalışma Süresi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ort. Paket/Saat</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($courierReport as $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium text-sm">{{ strtoupper(substr($data['courier']->name, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $data['courier']->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $data['shift_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                            {{ number_format($data['total_packages']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ round($data['total_minutes'] / 60, 1) }} saat
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($data['total_minutes'] > 0)
                                {{ round($data['total_packages'] / ($data['total_minutes'] / 60), 1) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Bu tarih aralığında veri bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Mobile: Card View -->
    <div class="md:hidden divide-y divide-gray-200">
        @forelse($courierReport as $data)
            <div class="p-4">
                <!-- Kurye Header -->
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($data['courier']->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">{{ $data['courier']->name }}</p>
                        <p class="text-xs text-gray-500">{{ $data['shift_count'] }} vardiya</p>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-2 bg-gray-50 rounded-lg p-3 text-center">
                    <div>
                        <p class="text-xs text-gray-500">Paket</p>
                        <p class="text-lg font-bold text-indigo-600">{{ number_format($data['total_packages']) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Saat</p>
                        <p class="text-lg font-bold text-gray-800">{{ round($data['total_minutes'] / 60, 1) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Paket/Saat</p>
                        <p class="text-lg font-bold text-green-600">
                            @if($data['total_minutes'] > 0)
                                {{ round($data['total_packages'] / ($data['total_minutes'] / 60), 1) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p>Bu tarih aralığında veri bulunamadı.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
