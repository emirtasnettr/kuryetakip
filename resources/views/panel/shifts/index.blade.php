@extends('layouts.panel')

@section('title', 'Tüm Vardiyalar')

@section('content')

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-4 md:p-6 mb-6">
    <form method="GET" action="{{ route('panel.shifts.index') }}">
        <!-- Mobile: Collapsible Filters -->
        <div class="md:hidden">
            <button type="button" onclick="toggleFilters()" class="w-full flex items-center justify-between text-gray-700 font-medium py-2">
                <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filtreler
                </span>
                <svg id="filter-arrow" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
        
        <!-- Filter Fields -->
        <div id="filter-fields" class="hidden md:grid grid-cols-1 md:grid-cols-5 gap-4 mt-4 md:mt-0">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç Tarihi</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tümü</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kurye</label>
                <select name="courier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tümü</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}" {{ request('courier_id') == $courier->id ? 'selected' : '' }}>
                            {{ $courier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                    Filtrele
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'status', 'courier_id']))
                    <a href="{{ route('panel.shifts.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Desktop: Table View -->
<div class="hidden lg:block bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurye</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bölge</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlangıç</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bitiş</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Süre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('panel.shifts.show', $shift) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-medium text-sm">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $shift->user->name }}</p>
                                    @if($shift->user->employee_code)
                                        <p class="text-xs text-gray-500">{{ $shift->user->employee_code }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $shift->district?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $shift->started_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $shift->ended_at?->format('d.m.Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $shift->formatted_duration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                            {{ $shift->package_count ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $shift->status == 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $shift->status == 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $shift->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ $shift->status == 'active' ? 'Aktif' : '' }}
                                {{ $shift->status == 'completed' ? 'Tamamlandı' : '' }}
                                {{ $shift->status == 'cancelled' ? 'İptal' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="{{ route('panel.shifts.show', $shift) }}" class="text-indigo-600 hover:text-indigo-900">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            Vardiya kaydı bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($shifts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $shifts->links() }}
        </div>
    @endif
</div>

<!-- Mobile & Tablet: Card View -->
<div class="lg:hidden space-y-4">
    @forelse($shifts as $shift)
        <a href="{{ route('panel.shifts.show', $shift) }}" class="block bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
            <!-- Header: Kurye + Durum -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        {{ $shift->status == 'active' ? 'bg-green-100' : '' }}
                        {{ $shift->status == 'completed' ? 'bg-indigo-100' : '' }}
                        {{ $shift->status == 'cancelled' ? 'bg-red-100' : '' }}
                    ">
                        <span class="font-semibold text-sm
                            {{ $shift->status == 'active' ? 'text-green-600' : '' }}
                            {{ $shift->status == 'completed' ? 'text-indigo-600' : '' }}
                            {{ $shift->status == 'cancelled' ? 'text-red-600' : '' }}
                        ">{{ strtoupper(substr($shift->user->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">{{ $shift->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $shift->district?->name ?? 'Bölge belirtilmedi' }}</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $shift->status == 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $shift->status == 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $shift->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ $shift->status == 'active' ? 'Aktif' : '' }}
                    {{ $shift->status == 'completed' ? 'Tamamlandı' : '' }}
                    {{ $shift->status == 'cancelled' ? 'İptal' : '' }}
                </span>
            </div>
            
            <!-- Info Grid -->
            <div class="grid grid-cols-3 gap-3 text-center bg-gray-50 rounded-lg p-3">
                <div>
                    <p class="text-xs text-gray-500">Başlangıç</p>
                    <p class="text-sm font-medium text-gray-800">{{ $shift->started_at->format('H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ $shift->started_at->format('d.m.Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Süre</p>
                    <p class="text-sm font-medium text-gray-800">{{ $shift->formatted_duration }}</p>
                    @if($shift->ended_at)
                        <p class="text-xs text-gray-400">{{ $shift->ended_at->format('H:i') }}'e kadar</p>
                    @else
                        <p class="text-xs text-green-500">Devam ediyor</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500">Paket</p>
                    <p class="text-sm font-bold text-indigo-600">{{ $shift->package_count ?? '-' }}</p>
                    <p class="text-xs text-gray-400">adet</p>
                </div>
            </div>
            
            <!-- Footer: Detay Arrow -->
            <div class="flex items-center justify-end mt-3 text-indigo-600 text-sm">
                <span>Detay</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-gray-500">Vardiya kaydı bulunamadı.</p>
        </div>
    @endforelse
    
    <!-- Mobile Pagination -->
    @if($shifts->hasPages())
        <div class="bg-white rounded-xl shadow-sm p-4">
            {{ $shifts->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function toggleFilters() {
        const fields = document.getElementById('filter-fields');
        const arrow = document.getElementById('filter-arrow');
        
        fields.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }
    
    // If filters are applied, show filter section on mobile
    @if(request()->hasAny(['start_date', 'end_date', 'status', 'courier_id']))
        document.addEventListener('DOMContentLoaded', function() {
            const fields = document.getElementById('filter-fields');
            const arrow = document.getElementById('filter-arrow');
            if (window.innerWidth < 768) {
                fields.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            }
        });
    @endif
</script>
@endpush
