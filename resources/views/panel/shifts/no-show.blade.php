@extends('layouts.panel')

@section('title', 'Vardiyaya Girmeyenler')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Vardiyaya Girmeyenler</h1>
        <p class="text-gray-500 mt-1">Seçilen tarih aralığında vardiyası atanmış ancak vardiyayı başlatmamış kuryeler.</p>
    </div>
    <div class="flex flex-wrap items-end gap-2">
        <form action="{{ route('panel.shifts.no-show') }}" method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label for="start_date" class="block text-xs font-medium text-gray-500 mb-0.5">Başlangıç</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                       class="rounded-lg border-gray-300 text-sm px-3 py-2">
            </div>
            <div>
                <label for="end_date" class="block text-xs font-medium text-gray-500 mb-0.5">Bitiş</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                       class="rounded-lg border-gray-300 text-sm px-3 py-2">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Göster</button>
        </form>
        <a href="{{ route('panel.shifts.no-show.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($assignments->isEmpty())
        <div class="p-8 text-center text-gray-500">
            <p>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }} aralığında vardiyaya girmeyen kurye bulunamadı.</p>
            <p class="text-sm mt-2">Tüm atamalar vardiyaya girmiş veya bu tarihte atama yok.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sicil No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-posta</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bölge</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vardiya saati</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vardiya adı</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atayan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atama durumu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notlar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($assignments as $assignment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $assignment->scheduledShift->shift_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('panel.couriers.show', $assignment->courier) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    {{ $assignment->courier->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->courier->employee_code ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->courier->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->courier->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $assignment->scheduledShift->region->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($assignment->scheduledShift->start_time)->format('H:i') }}
                                –
                                {{ \Carbon\Carbon::parse($assignment->scheduledShift->end_time)->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600" title="{{ $assignment->scheduledShift->title ?? '' }}">
                                {{ $assignment->scheduledShift->title ? \Illuminate\Support\Str::limit($assignment->scheduledShift->title, 40) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $assignment->assignedByUser?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($assignment->status === \App\Models\ShiftAssignment::STATUS_NO_SHOW)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">Gelmedi</span>
                                @elseif($assignment->status === \App\Models\ShiftAssignment::STATUS_CONFIRMED)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-amber-100 text-amber-800">Onayladı, girmedi</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-800">Atandı, girmedi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs" title="{{ $assignment->notes ?? '' }}">
                                @if($assignment->notes)
                                    <span class="line-clamp-2">{{ $assignment->notes }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 text-sm text-gray-600">
            Toplam <strong>{{ $assignments->count() }}</strong> kurye vardiyaya girmemiş.
        </div>
    @endif
</div>
@endsection
