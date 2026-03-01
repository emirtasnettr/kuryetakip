@extends('layouts.panel')

@section('title', 'Vardiya Detayı')

@section('content')

<div class="mb-4">
    <a href="{{ route('panel.schedule.index', ['date' => $scheduledShift->shift_date->format('Y-m-d')]) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Listeye dön
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

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Vardiya bilgisi</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <dt class="text-gray-500">Bölge</dt>
        <dd class="font-medium text-gray-900">{{ $scheduledShift->region?->name ?? '—' }}</dd>
        <dt class="text-gray-500">Tarih</dt>
        <dd class="font-medium text-gray-900">{{ $scheduledShift->shift_date->translatedFormat('d F Y') }}</dd>
        <dt class="text-gray-500">Saat</dt>
        <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($scheduledShift->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($scheduledShift->end_time)->format('H:i') }}</dd>
        <dt class="text-gray-500">Gerekli kurye</dt>
        <dd class="font-medium text-gray-900">{{ $scheduledShift->required_couriers }}</dd>
        <dt class="text-gray-500">Durum</dt>
        <dd>
            @if($scheduledShift->status === \App\Models\ScheduledShift::STATUS_PUBLISHED)
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">Yayında</span>
            @elseif($scheduledShift->status === \App\Models\ScheduledShift::STATUS_COMPLETED)
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-800">Tamamlandı</span>
            @elseif($scheduledShift->status === \App\Models\ScheduledShift::STATUS_CANCELLED)
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">İptal</span>
            @else
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-800">{{ $scheduledShift->status }}</span>
            @endif
        </dd>
    </dl>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Atanmış kuryeler ({{ $scheduledShift->activeAssignments->count() }} / {{ $scheduledShift->required_couriers }})</h2>
    @if($scheduledShift->activeAssignments->isEmpty())
        <p class="text-sm text-gray-500">Henüz atanmış kurye yok.</p>
    @else
        <ul class="space-y-2">
            @foreach($scheduledShift->activeAssignments as $a)
                <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <span class="font-medium text-gray-900">{{ $a->courier?->name ?? '—' }}</span>
                        @if($a->courier?->phone)
                            <span class="text-gray-500 text-sm ml-2">{{ $a->courier->phone }}</span>
                        @endif
                    </div>
                    <form action="{{ route('panel.schedule.shifts.unassign', [$scheduledShift, $a]) }}" method="POST" class="inline" onsubmit="return confirm('Bu kuryeyi vardiyadan çıkarmak istediğinize emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Çıkar</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>

@php
    $availableCouriers = $couriers->whereNotIn('id', $scheduledShift->activeAssignments->pluck('courier_id'));
@endphp
@if($scheduledShift->activeAssignments->count() < $scheduledShift->required_couriers && !in_array($scheduledShift->status, [\App\Models\ScheduledShift::STATUS_CANCELLED, \App\Models\ScheduledShift::STATUS_COMPLETED], true) && $availableCouriers->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kurye ata</h2>
    <form action="{{ route('panel.schedule.shifts.assign', $scheduledShift) }}" method="POST" class="flex flex-wrap items-end gap-4">
        @csrf
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kurye</label>
            <select name="courier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                <option value="">Seçin</option>
                @foreach($couriers as $c)
                    @php $already = $scheduledShift->activeAssignments->contains('courier_id', $c->id); @endphp
                    <option value="{{ $c->id }}" {{ $already ? 'disabled' : '' }}>{{ $c->name }}{{ $already ? ' (zaten atanmış)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Not (isteğe bağlı)</label>
            <input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Not">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Ata</button>
    </form>
</div>
@elseif($scheduledShift->activeAssignments->count() < $scheduledShift->required_couriers && !in_array($scheduledShift->status, [\App\Models\ScheduledShift::STATUS_CANCELLED, \App\Models\ScheduledShift::STATUS_COMPLETED], true) && $availableCouriers->isEmpty())
<div class="bg-white rounded-xl shadow-sm p-6">
    <p class="text-sm text-gray-500">Atanabilecek başka kurye yok (hepsi atanmış veya bölgede kurye tanımlı değil).</p>
</div>
@endif

@endsection
