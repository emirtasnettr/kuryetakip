@extends('layouts.panel')

@section('title', 'Bölge Raporu')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <h1 class="text-xl font-semibold">Bölge Raporu</h1>
        <p class="text-sm text-gray-500 mt-1">Bölgelere göre planlanan vardiya saati ile gerçekleşen vardiya saati karşılaştırması. Eksik = planlanan &gt; gerçekleşen (örn. geç giriş).</p>
    </div>

    <!-- Tarih filtresi -->
    <div class="p-4 border-b bg-gray-50">
        <form method="GET" action="{{ route('panel.regions.report') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Başlangıç</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bitiş</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
            </div>
            <button type="submit" class="px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800">
                Filtrele
            </button>
            <a href="{{ route('panel.regions.report') }}" class="px-4 py-2 text-gray-600 text-sm hover:text-gray-800">
                Sıfırla
            </a>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bölge</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Planlanan (saat)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gerçekleşen (saat)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fark</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Durum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <span class="font-medium text-gray-800">{{ $row['region_name'] }}</span>
                            @if($row['city'])
                                <span class="text-gray-500 text-sm">({{ $row['city'] }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right text-sm">{{ $row['planned_formatted'] }}</td>
                        <td class="px-6 py-3 text-right text-sm">{{ $row['actual_formatted'] }}</td>
                        <td class="px-6 py-3 text-right text-sm">
                            @if($row['diff_minutes'] !== 0)
                                {{ $row['diff_minutes'] > 0 ? '−' : '+' }}{{ $row['diff_formatted'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($row['diff_minutes'] > 0)
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">Eksik</span>
                            @elseif($row['diff_minutes'] < 0)
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">Fazla</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Tam</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Bu tarih aralığında bölge bulunamadı veya veri yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
