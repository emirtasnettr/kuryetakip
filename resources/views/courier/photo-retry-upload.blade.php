@extends('layouts.courier')

@section('title', 'Fotoğraf Talebi')

@section('content')
<div class="p-4 space-y-6">
    <div>
        <a href="{{ route('courier.photo-retry') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1">← Fotoğraf Talepleri</a>
        <h1 class="text-xl font-bold text-gray-800 mt-2">Vardiya Başlangıç Fotoğrafı Yükle</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $shift->started_at->translatedFormat('d F Y') }} · {{ $shift->started_at->format('H:i') }}{{ $shift->ended_at ? ' - ' . $shift->ended_at->format('H:i') : '' }}</p>
    </div>

    <form action="{{ route('courier.photo-retry.upload.submit', $shift) }}" method="POST" enctype="multipart/form-data" id="photoRetryForm" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        @csrf
        <p class="text-sm text-gray-600">Fotoğraf çek butonuna basınca telefonun kamera uygulaması açılır; oradan fotoğraf çekin. Mevcut fotoğraflar silinmeyecek; inceleme sonrası tekrar Vardiya Uyumluluk İncelemesi'ne düşecektir.</p>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vardiya başlangıç fotoğrafı (tekrar) *</label>
            <input type="file" name="photo_start" accept="image/*" capture="environment" class="hidden" id="photoRetryInput" required>
            <div id="photoRetryStartArea" class="mb-3">
                <button type="button" id="photoRetryOpenCamera" class="flex items-center justify-center w-full py-4 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-black hover:text-black bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Fotoğraf Çek
                </button>
            </div>
            <div id="photoRetryPreview" class="hidden mb-3">
                <img id="photoRetryPreviewImg" src="" alt="Önizleme" class="w-full h-48 object-cover rounded-lg">
                <button type="button" id="photoRetryRetake" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Yeniden Çek</button>
            </div>
            @error('photo_start')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" id="photoRetrySubmit" disabled class="w-full py-3 bg-black text-white font-semibold rounded-lg hover:bg-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed">Gönder</button>
    </form>
</div>

@push('scripts')
<script>
(function() {
    document.getElementById('photoRetryOpenCamera').addEventListener('click', function() {
        document.getElementById('photoRetryInput').click();
    });
    document.getElementById('photoRetryInput').addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        document.getElementById('photoRetryPreviewImg').src = URL.createObjectURL(file);
        document.getElementById('photoRetryPreview').classList.remove('hidden');
        document.getElementById('photoRetryStartArea').classList.add('hidden');
        document.getElementById('photoRetrySubmit').disabled = false;
    });
    document.getElementById('photoRetryRetake').addEventListener('click', function() {
        document.getElementById('photoRetryPreview').classList.add('hidden');
        document.getElementById('photoRetryStartArea').classList.remove('hidden');
        document.getElementById('photoRetryInput').value = '';
        document.getElementById('photoRetrySubmit').disabled = true;
    });
})();
</script>
@endpush
@endsection
