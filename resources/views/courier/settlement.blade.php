@extends('layouts.courier')

@section('title', 'Hakedişim')
@section('back_url', route('courier.home'))

@section('content')
<div class="p-4 max-w-2xl mx-auto">
    <p class="text-gray-500 text-sm mb-4">Seçtiğiniz tarih aralığındaki kazanç özetiniz.</p>

    <form action="{{ route('courier.settlement') }}" method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-end gap-2">
            <div class="flex-1 min-w-[120px]">
                <label for="start_date" class="block text-xs font-medium text-gray-500 mb-0.5">Başlangıç</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label for="end_date" class="block text-xs font-medium text-gray-500 mb-0.5">Bitiş</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800">Göster</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-semibold text-gray-800">Özet</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
        </div>
        <ul class="divide-y divide-gray-100">
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Vardiya sayısı</span>
                <span class="text-sm font-medium text-gray-900">{{ $data['shift_count'] }}</span>
            </li>
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Çalışma süresi (saat)</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($data['hours'], 2, ',', '.') }}</span>
            </li>
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Süre tutarı</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($data['hourly_earnings'], 2, ',', '.') }} TL</span>
            </li>
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Paket sayısı</span>
                <span class="text-sm font-medium text-gray-900">{{ $data['package_count'] }}</span>
            </li>
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Paket tutarı</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($data['package_earnings'], 2, ',', '.') }} TL</span>
            </li>
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Vardiya uyumluluk primi ({{ $data['photo_bonus_count'] }} adet)</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($data['photo_bonus_total'], 2, ',', '.') }} TL</span>
            </li>
            @if(isset($data['deduct_from_settlement_total']) && $data['deduct_from_settlement_total'] > 0)
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Hakedişten düşülen</span>
                <span class="text-sm font-medium text-amber-700">− {{ number_format($data['deduct_from_settlement_total'], 2, ',', '.') }} TL</span>
            </li>
            @endif
            @if(isset($data['deduction_total']) && $data['deduction_total'] > 0)
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Kesinti</span>
                <span class="text-sm font-medium text-red-600">− {{ number_format($data['deduction_total'], 2, ',', '.') }} TL</span>
            </li>
            @endif
            @if(isset($data['extra_bonus_total']) && $data['extra_bonus_total'] > 0)
            <li class="flex justify-between items-center px-4 py-3">
                <span class="text-sm text-gray-600">Ek prim</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($data['extra_bonus_total'], 2, ',', '.') }} TL</span>
            </li>
            @endif
        </ul>
        <div class="px-4 py-4 bg-gray-50 border-t-2 border-gray-200 flex justify-between items-center">
            <span class="font-semibold text-gray-800">Toplam</span>
            <span class="text-xl font-bold text-gray-900">{{ number_format($data['total'], 2, ',', '.') }} TL</span>
        </div>
    </div>

    <p class="mt-4 text-xs text-gray-500">Tutarlar KDV dahil gösterilir. Birim fiyatlar yönetim tarafından belirlenir.</p>
</div>
@endsection
