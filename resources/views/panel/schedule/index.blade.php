@extends('layouts.panel')

@section('title', 'Vardiya Planlama')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Vardiya Planlama</h1>
    <a href="{{ route('panel.schedule.shift-template') }}" class="inline-flex items-center px-3 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        Excel Şablonu
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-800 text-sm">
        <ul class="list-disc list-inside">{{ implode('', $errors->all('<li>:message</li>')) }}</ul>
    </div>
@endif

{{-- Gün navigasyonu (önceki / sonraki) --}}
@php
    $prevDate = $dateObj->copy()->subDay()->format('Y-m-d');
    $nextDate = $dateObj->copy()->addDay()->format('Y-m-d');
    $queryParams = array_filter(['date' => '', 'region_id' => $regionId], fn($v) => $v !== '' && $v !== null);
@endphp
<div class="flex items-center justify-center gap-4 mb-4">
    <a href="{{ route('panel.schedule.index', array_merge($queryParams, ['date' => $prevDate])) }}"
       class="flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors"
       title="Önceki gün">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <span class="text-lg font-semibold text-gray-900 min-w-[180px] text-center">{{ $dateObj->translatedFormat('d F Y, l') }}</span>
    <a href="{{ route('panel.schedule.index', array_merge($queryParams, ['date' => $nextDate])) }}"
       class="flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors"
       title="Sonraki gün">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </a>
</div>

{{-- Filtreler --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('panel.schedule.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
            <input type="date" name="date" value="{{ $date }}"
                   class="w-full min-w-[160px] px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bölge</label>
            <select name="region_id" class="w-full min-w-[180px] px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Tümü</option>
                @foreach($regions as $r)
                    <option value="{{ $r->id }}" {{ (string)$regionId === (string)$r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Göster</button>
    </form>
</div>

{{-- Yeni vardiya formu --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-200">
    <button type="button" onclick="document.getElementById('new-shift-form').classList.toggle('hidden')"
            class="flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Yeni vardiya ekle
    </button>
    <form id="new-shift-form" action="{{ route('panel.schedule.shifts.store') }}" method="POST" class="mt-4 hidden">
        @csrf
        <input type="hidden" name="from" value="list">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                <input type="date" name="shift_date" value="{{ $date }}" min="{{ now()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bölge</label>
                <select name="region_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">Seçin</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Başlangıç</label>
                <input type="time" name="start_time" value="09:00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş</label>
                <input type="time" name="end_time" value="18:00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gerekli kurye</label>
                <input type="number" name="required_couriers" value="1" min="1" max="50"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Kaydet</button>
        </div>
    </form>
</div>

{{-- Bölgesel kartlar --}}
@php
    $shiftsByRegion = $shifts->groupBy('region_id');
@endphp

@if($shiftsByRegion->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500 text-sm">
        {{ $dateObj->translatedFormat('d F Y') }} için seçilen filtrede (tamamlanmamış) vardiya yok.
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($shiftsByRegion as $regionId => $regionShifts)
            @php
                $region = $regionShifts->first()->region;
                $regionName = $region ? $region->name : 'Bölge #' . $regionId;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-base font-semibold text-gray-900">{{ $regionName }}</h2>
                </div>
                <div class="p-4 flex-1">
                    <ul class="space-y-3">
                        @foreach($regionShifts as $s)
                            @php
                                $assigned = $s->activeAssignments->count();
                                $required = (int) $s->required_couriers;
                            @endphp
                            <li class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 last:border-0 last:pb-0">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Kurye: {{ $assigned }} / {{ $required }}
                                        @if($s->status === \App\Models\ScheduledShift::STATUS_PUBLISHED)
                                            <span class="inline-flex px-1.5 py-0.5 rounded bg-green-100 text-green-800 ml-1">Yayında</span>
                                        @elseif($s->status === \App\Models\ScheduledShift::STATUS_CANCELLED)
                                            <span class="inline-flex px-1.5 py-0.5 rounded bg-red-100 text-red-800 ml-1">İptal</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    <a href="{{ route('panel.schedule.shifts.page', $s) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Görüntüle</a>
                                    <form action="{{ route('panel.schedule.shifts.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Bu vardiyayı silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="from" value="list">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
