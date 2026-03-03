@extends('layouts.panel')

@section('title', 'Geçmiş Masraf Talepleri')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Geçmiş Masraf Talepleri</h1>
        <p class="text-gray-500 mt-1">Tüm masraf talepleri — sipariş no, nereden alındı, ürünler ve onay bilgileri.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.expenses.history.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Excel İndir
        </a>
        <a href="{{ route('panel.expenses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            ← Masraf Talepleri
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Kurye</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Sipariş No</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Alınan İşletme İsmi</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Dış Alım Yapma Sebebi</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Kalemler (Ürün – Adet/KG – Fiyat)</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Toplam (TL)</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Durum</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Onay Tipi</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Onaylayan</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Onay Tarihi</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Oluşturulma</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase whitespace-nowrap">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $req->user->name ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $req->order_number ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $req->source ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700 max-w-xs">{{ Str::limit($req->reason, 80) ?: '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">
                            @if($req->items->isNotEmpty())
                                <ul class="list-none space-y-0.5">
                                    @foreach($req->items as $item)
                                        <li>{{ $item->product_name }} — {{ $item->quantity_or_kg ?: '-' }} — {{ number_format($item->price, 2, ',', '.') }} TL</li>
                                    @endforeach
                                </ul>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700 text-right whitespace-nowrap">{{ number_format($req->total_amount, 2, ',', '.') }} TL</td>
                        <td class="px-3 py-3 text-sm whitespace-nowrap">
                            @if($req->isPending())
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-amber-100 text-amber-800">Beklemede</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">Onaylandı</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap">
                            @if($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)
                                Hakedişten düşüldü
                            @elseif($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_CLOSED)
                                Tamamlandı
                            @elseif($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_SETTLEMENT)
                                Hakedişe eklendi
                            @elseif($req->approval_type)
                                Havale
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $req->approvedByUser->name ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $req->approved_at ? $req->approved_at->translatedFormat('d M Y H:i') : '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $req->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-3 py-3 text-sm text-right whitespace-nowrap">
                            <a href="{{ route('panel.expenses.show', $req) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Görüntüle</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-200">{{ $requests->links() }}</div>
</div>
@endsection
