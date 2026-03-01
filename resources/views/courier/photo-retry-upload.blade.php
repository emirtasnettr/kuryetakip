@extends('layouts.courier')

@section('title', 'Fotoğraf Talebi')

@section('content')
<div class="p-4 space-y-6">
    <div>
        <a href="{{ route('courier.photo-retry') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1">← Fotoğraf Talepleri</a>
        <h1 class="text-xl font-bold text-gray-800 mt-2">Vardiya Başlangıç Fotoğrafı Yükle</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $shift->started_at->translatedFormat('d F Y') }} · {{ $shift->started_at->format('H:i') }}{{ $shift->ended_at ? ' - ' . $shift->ended_at->format('H:i') : '' }}</p>
    </div>

    <form action="{{ route('courier.photo-retry.upload.submit', $shift) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        @csrf
        <p class="text-sm text-gray-600">Sadece vardiya başlangıç fotoğrafını tekrar yükleyin. Mevcut fotoğraflar silinmeyecek; inceleme sonrası tekrar Vardiya Uyumluluk İncelemesi'ne düşecektir.</p>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vardiya başlangıç fotoğrafı (tekrar) *</label>
            <input type="file" name="photo_start" accept="image/*" capture="environment" required
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
            @error('photo_start')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full py-3 bg-black text-white font-semibold rounded-lg hover:bg-gray-800">Gönder</button>
    </form>
</div>
@endsection
