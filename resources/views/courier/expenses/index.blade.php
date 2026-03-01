@extends('layouts.courier')

@section('title', 'Masraf Taleplerim')
@section('back_url', route('courier.home'))

@section('content')
<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Masraf Taleplerim</h1>
        <a href="{{ route('courier.expenses.create') }}" class="px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800">
            Yeni talep
        </a>
    </div>

    <div class="space-y-3">
        @forelse($requests as $req)
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-gray-800">{{ number_format($req->total_amount, 2, ',', '.') }} TL</p>
                        @if($req->order_number)
                            <p class="text-sm text-gray-600">Sipariş: {{ $req->order_number }}</p>
                        @endif
                        <p class="text-sm text-gray-500">{{ $req->created_at->translatedFormat('d M Y H:i') }}</p>
                        @if($req->isPending())
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded bg-amber-100 text-amber-800">İncelemede</span>
                        @else
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">
                                Onaylandı
                                @if($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)
                                    (Hakedişten düşüldü)
                                @elseif($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_CLOSED)
                                    (Tamamlandı)
                                @elseif($req->approval_type === \App\Models\ExpenseRequest::APPROVAL_SETTLEMENT)
                                    (Hakedişe eklendi)
                                @else
                                    (Havale)
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
                Henüz masraf talebiniz yok.
                <a href="{{ route('courier.expenses.create') }}" class="block mt-2 text-black font-medium">Yeni talep oluştur</a>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
