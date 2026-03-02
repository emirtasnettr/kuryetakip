@extends('layouts.panel')

@section('title', 'Masraf Talebi İnceleme')

@section('content')
<div class="mb-6">
    <a href="{{ route('panel.expenses.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">← Masraf Talepleri</a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Masraf Talebi #{{ $expenseRequest->id }}</h1>
            <p class="text-gray-500 mt-1">{{ $expenseRequest->user->name }} · {{ $expenseRequest->created_at->translatedFormat('d F Y H:i') }}</p>
        </div>
        <div class="text-2xl font-semibold text-gray-900">{{ number_format($expenseRequest->total_amount, 2, ',', '.') }} TL</div>
    </div>

    @if($expenseRequest->order_number || $expenseRequest->reason || $expenseRequest->source)
    <div class="mb-6">
        @if($expenseRequest->order_number)
            <p class="text-sm font-medium text-gray-700 mb-1">Sipariş numarası</p>
            <p class="text-gray-900 mb-3">{{ $expenseRequest->order_number }}</p>
        @endif
        @if($expenseRequest->source)
            <p class="text-sm font-medium text-gray-700 mb-1">Nereden Alındı</p>
            <p class="text-gray-900 mb-3">{{ $expenseRequest->source }}</p>
        @endif
        @if($expenseRequest->reason)
            <p class="text-sm font-medium text-gray-700 mb-1">Neden</p>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $expenseRequest->reason }}</p>
        @endif
    </div>
    @endif

    <div class="mb-6">
        <p class="text-sm font-medium text-gray-700 mb-2">Fiş fotoğrafı</p>
        <a href="{{ $expenseRequest->receipt_photo_url }}" target="_blank" class="inline-block rounded-lg border border-gray-200 overflow-hidden">
            <img src="{{ $expenseRequest->receipt_photo_url }}" alt="Fiş" class="max-h-64 w-auto object-contain">
        </a>
    </div>

    <div class="mb-6">
        <p class="text-sm font-medium text-gray-700 mb-2">Kalemler</p>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ürün Adı</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Adet/KG</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Fiyat</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($expenseRequest->items as $item)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product_name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $item->quantity_or_kg }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600 text-right">{{ number_format($item->price, 2, ',', '.') }} TL</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-2 text-sm font-semibold text-gray-800">Toplam: {{ number_format($expenseRequest->total_amount, 2, ',', '.') }} TL</p>
    </div>

    @if($expenseRequest->isPending())
        <div class="border-t border-gray-200 pt-6">
            <form action="{{ route('panel.expenses.approve', $expenseRequest) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="approval_type" value="{{ \App\Models\ExpenseRequest::APPROVAL_CLOSED }}">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    Onayla
                </button>
            </form>
        </div>
    @else
        <div class="border-t border-gray-200 pt-4 mt-4 text-sm text-gray-500">
            Onaylandı: {{ $expenseRequest->approved_at->translatedFormat('d M Y H:i') }}
            @if($expenseRequest->approvedByUser)
                · {{ $expenseRequest->approvedByUser->name }}
            @endif
            ·
            @if($expenseRequest->approval_type === \App\Models\ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)
                Hakedişten düşüldü
            @elseif($expenseRequest->approval_type === \App\Models\ExpenseRequest::APPROVAL_CLOSED)
                Onaylandı
            @elseif($expenseRequest->approval_type === \App\Models\ExpenseRequest::APPROVAL_DEBT_BALANCE)
                Borç bakiyesi (artık kullanılmıyor)
            @elseif($expenseRequest->approval_type === \App\Models\ExpenseRequest::APPROVAL_SETTLEMENT)
                Hakedişe eklendi (eski)
            @else
                Havale ile gönderildi (eski)
            @endif
        </div>
    @endif
</div>
@endsection
