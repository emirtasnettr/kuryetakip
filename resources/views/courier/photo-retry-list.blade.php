@extends('layouts.courier')

@section('title', 'Fotoğraf Talepleri')

@section('content')
<div class="p-4 space-y-6">
    <div>
        <a href="{{ route('courier.home') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1">
            ← Ana sayfa
        </a>
        <h1 class="text-xl font-bold text-gray-800 mt-2">Fotoğraf Talepleri</h1>
        <p class="text-gray-500 text-sm mt-1">Yönetici aşağıdaki vardiyalar için tekrar vardiya başlangıç fotoğrafı yüklemenizi istedi. Mevcut fotoğraflar silinmeyecek.</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    @if($shifts->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-500">
            Tekrar fotoğraf talebiniz yok.
        </div>
    @else
        <div class="space-y-3">
            @foreach($shifts as $shift)
                <div class="bg-white rounded-xl shadow-sm p-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $shift->started_at->translatedFormat('d F Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $shift->started_at->format('H:i') }} - {{ $shift->ended_at?->format('H:i') }}</p>
                    </div>
                    <a href="{{ route('courier.photo-retry.upload', $shift) }}" class="px-4 py-2 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800">
                        Başlangıç fotoğrafı yükle
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
