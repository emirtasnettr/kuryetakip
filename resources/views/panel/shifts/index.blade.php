@extends('layouts.panel')

@section('title', 'Tüm Vardiyalar')

@section('content')

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('panel.shifts.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
        <div class="flex items-end">
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                Filtrele
            </button>
        </div>
    </form>
</div>

<!-- Shifts Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                    <tr class="hover:bg-gray-50">
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
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $shifts->links() }}
    </div>
</div>

@endsection
