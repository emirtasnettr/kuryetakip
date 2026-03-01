@extends('layouts.panel')

@section('title', 'Vardiya Uyumluluk Raporu')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Vardiya Uyumluluk Raporu</h1>
        <p class="text-gray-500 mt-1">Kuryelerin uyumlu ve uyumsuz vardiya sayılarını tarih aralığına göre görüntüleyin.</p>
    </div>
    <div class="flex flex-wrap items-end gap-2">
        <form action="{{ route('panel.settlement.photo-compliance-report') }}" method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label for="start_date" class="block text-xs font-medium text-gray-500 mb-0.5">Başlangıç</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                       class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-xs font-medium text-gray-500 mb-0.5">Bitiş</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                       class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="name_search" class="block text-xs font-medium text-gray-500 mb-0.5">Kurye adı</label>
                <input type="text" name="name" id="name_search" value="{{ old('name', $nameSearch ?? '') }}"
                       placeholder="İsimle ara…"
                       class="rounded-lg border-gray-300 text-sm min-w-[140px]">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filtrele</button>
            @if(!empty($nameSearch ?? ''))
                <a href="{{ route('panel.settlement.photo-compliance-report', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-3 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg text-sm">Temizle</a>
            @endif
        </form>
        <a href="{{ route('panel.settlement.photo-compliance-report.export', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'name' => $nameSearch ?? ''])) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurye</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Uyumlu vardiya</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Uyumsuz vardiya</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bekleyen</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam vardiya</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['courier']->name }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="font-semibold text-green-600">{{ $row['uyumlu'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="font-semibold text-red-600">{{ $row['uyumsuz'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['bekleyen'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $row['toplam'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Kurye bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(!empty($rows))
            <tfoot class="bg-gray-100 font-semibold">
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">Toplam</td>
                    <td class="px-4 py-3 text-sm text-green-600 text-right">{{ collect($rows)->sum('uyumlu') }}</td>
                    <td class="px-4 py-3 text-sm text-red-600 text-right">{{ collect($rows)->sum('uyumsuz') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ collect($rows)->sum('bekleyen') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ collect($rows)->sum('toplam') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if(!empty($rows))
<div class="mt-4 text-sm text-gray-500">
    <p><span class="text-green-600 font-medium">Uyumlu:</span> Vardiya uyumluluk primi onaylanan vardiya · <span class="text-red-600 font-medium">Uyumsuz:</span> Prim verilmeyen vardiya · <span class="text-gray-600">Bekleyen:</span> İnceleme veya tekrar yükleme bekleyen</p>
</div>
@endif
@endsection
