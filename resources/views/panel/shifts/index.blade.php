@extends('layouts.panel')

@section('title', 'Tüm Vardiyalar')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-3 mb-3">
    <h1 class="text-lg font-semibold text-gray-900">Vardiya Raporları</h1>
    <a href="{{ route('panel.shifts.export', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        Excel
    </a>
</div>

<!-- Filtreler: tek satır kompakt -->
<div class="bg-white rounded-lg shadow-sm p-3 mb-3">
    <form method="GET" action="{{ route('panel.shifts.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
        <span class="text-gray-400 text-sm">–</span>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
        <select name="status" class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Durum: Tümü</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Tamamlandı</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal</option>
        </select>
        <input type="text" name="name" value="{{ request('name') }}" placeholder="Kurye adıyla ara…"
               class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 min-w-[140px]">
        <select name="courier_id" class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 min-w-[120px]">
            <option value="">Kurye: Tümü</option>
            @foreach($couriers as $courier)
                <option value="{{ $courier->id }}" {{ request('courier_id') == $courier->id ? 'selected' : '' }}>{{ $courier->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Filtrele</button>
        @if(request()->hasAny(['start_date', 'end_date', 'status', 'courier_id', 'name']))
            <a href="{{ route('panel.shifts.index') }}" class="px-2 py-1.5 text-gray-500 hover:text-gray-700 text-sm border border-gray-300 rounded" title="Filtreleri temizle">×</a>
        @endif
    </form>
</div>

<!-- Desktop: Kompakt tablo, ekrana sığan yükseklik + yatay kaydırma -->
<div class="hidden lg:block bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="overflow-auto max-h-[calc(100vh-220px)]">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap sticky left-0 bg-gray-50 z-20 min-w-[120px]">Kurye</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Bölge</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Başl.</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Bitiş</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Süre</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Paket</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap" title="Foto uyumluluk">Foto</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap max-w-[80px]">Not</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Durum</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 z-20">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('panel.shifts.show', $shift) }}'">
                        <td class="px-3 py-2 whitespace-nowrap sticky left-0 bg-white z-10 hover:bg-gray-50 border-r border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 text-indigo-600 font-medium">{{ strtoupper(substr($shift->user->name ?? '', 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $shift->user->name }}</p>
                                    @if($shift->user->employee_code)
                                        <p class="text-gray-500 truncate">{{ $shift->user->employee_code }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $shift->region?->name ?? ($shift->district?->name ?? '–') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $shift->started_at->format('d.m.y H:i') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $shift->ended_at?->format('d.m.y H:i') ?? '–' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ $shift->formatted_duration }}</td>
                        <td class="px-3 py-2 whitespace-nowrap font-medium text-indigo-600">{{ $shift->package_count ?? '–' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if($shift->status === 'completed' && $shift->photo_compliance_status)
                                @if($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_APPROVED)
                                    <span class="text-green-600" title="Onaylı">✓</span>
                                @elseif($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_NO_BONUS)
                                    <span class="text-amber-600" title="Prim yok">!</span>
                                @elseif($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_RE_REQUESTED)
                                    <span class="text-orange-600" title="Tekrar istenecek">↻</span>
                                @else
                                    <span class="text-gray-400" title="Beklemede">–</span>
                                @endif
                            @else
                                <span class="text-gray-300">–</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500 max-w-[80px] truncate" title="{{ $shift->notes ?? '' }}">{{ $shift->notes ?: '–' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="px-1.5 py-0.5 text-xs font-medium rounded
                                {{ $shift->status == 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $shift->status == 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $shift->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                            ">{{ $shift->status == 'active' ? 'Aktif' : ($shift->status == 'completed' ? 'Tamam' : 'İptal') }}</span>
                            @if($shift->auto_closed_at)
                                <span class="px-1.5 py-0.5 text-xs rounded bg-amber-100 text-amber-800" title="Otomatik kapatıldı">⏱</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right sticky right-0 bg-white z-10 hover:bg-gray-50 border-l border-gray-100">
                            <a href="{{ route('panel.shifts.show', $shift) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detay</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-8 text-center text-gray-500">Vardiya kaydı bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 bg-gray-50 text-xs">
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
                        <p class="text-xs text-gray-500">{{ $shift->user->employee_code ? 'Sicil: ' . $shift->user->employee_code : '' }}{{ $shift->user->employee_code && $shift->user->phone ? ' · ' : '' }}{{ $shift->user->phone ?? '' }}</p>
                        <p class="text-xs text-gray-500">{{ $shift->district?->name ?? '-' }} / {{ $shift->region?->name ?? '-' }}</p>
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
                @if($shift->auto_closed_at)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">Sistem tarafından kapatıldı</span>
                @endif
            </div>
            
            <!-- Info Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center bg-gray-50 rounded-lg p-3">
                <div>
                    <p class="text-xs text-gray-500">Başlangıç</p>
                    <p class="text-sm font-medium text-gray-800">{{ $shift->started_at->format('d.m.Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Bitiş</p>
                    <p class="text-sm font-medium text-gray-800">{{ $shift->ended_at ? $shift->ended_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Süre</p>
                    <p class="text-sm font-medium text-gray-800">{{ $shift->formatted_duration }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Paket</p>
                    <p class="text-sm font-bold text-indigo-600">{{ $shift->package_count ?? '-' }}</p>
                </div>
            </div>
            @if($shift->status === 'completed' && $shift->photo_compliance_status)
            <p class="text-xs text-gray-500 mt-2">
                Foto uyumluluk:
                @if($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_APPROVED)
                    <span class="text-green-600">Onaylı</span>
                @elseif($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_NO_BONUS)
                    <span class="text-amber-600">Prim yok</span>
                @elseif($shift->photo_compliance_status === \App\Models\Shift::PHOTO_COMPLIANCE_RE_REQUESTED)
                    <span class="text-orange-600">Tekrar istenecek</span>
                @else
                    <span class="text-gray-500">Beklemede</span>
                @endif
            </p>
            @endif
            @if($shift->notes)
            <p class="text-xs text-gray-600 mt-1 line-clamp-2" title="{{ $shift->notes }}">{{ $shift->notes }}</p>
            @endif
            
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
